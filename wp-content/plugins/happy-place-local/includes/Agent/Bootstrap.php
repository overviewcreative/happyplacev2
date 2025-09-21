<?php
namespace HappyPlace\Local\Agent;

class Bootstrap {
  public static function init() {
    // Initialize LLM Manager
    LLMManager::init();
    
    // Register private ingest CPT to store incoming items (no custom tables needed).
    add_action('init', [self::class, 'register_ingest_cpt']);
    // Cron tick
    add_action('hpl/agent/tick', [Runner::class, 'tick']);
    if (!wp_next_scheduled('hpl/agent/tick')) {
      wp_schedule_event(time()+120, 'ten_minutes', 'hpl/agent/tick');
    }
    // Custom interval
    add_filter('cron_schedules', function($s){ $s['ten_minutes']=['interval'=>600,'display'=>'Every 10 Minutes']; return $s; });
    // CLI
    if (defined('WP_CLI') && WP_CLI) {
      \WP_CLI::add_command('hpl:agent', CLI::class);
      \WP_CLI::add_command('hpl:ingest', CLIIngest::class);
      \WP_CLI::add_command('hpl:places', CLIPlaces::class);
    }
  }

  public static function register_ingest_cpt() {
    register_post_type('hpl_ingest', [
      'label' => 'HPL Ingest',
      'public' => false,
      'show_ui' => true,
      'show_in_menu' => 'tools.php',
      'supports' => ['title','editor','custom-fields'],
    ]);
  }
}
