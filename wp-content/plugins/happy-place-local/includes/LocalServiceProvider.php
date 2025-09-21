<?php
namespace HappyPlace\Local;

class LocalServiceProvider {
  public function register() {
    // Initialize configuration with constant overrides
    $this->setup_config_overrides();
    
    // CPTs
    add_action('init', [CPT\LocalPlace::class, 'register']);
    add_action('init', [CPT\Event::class, 'register']);

    // Admin configuration page
    new Admin\LocalConfigPage();

    // Admin fields (meta boxes) if ACF not present
    if (!function_exists('acf_add_local_field_group')) {
      add_action('add_meta_boxes', [Admin\EventMeta::class, 'register']);
      add_action('save_post', [Admin\EventMeta::class, 'save']);
      add_action('add_meta_boxes', [Admin\PlaceMeta::class, 'register']);
      add_action('save_post', [Admin\PlaceMeta::class, 'save']);
    }

    // Relations & denormalization
    add_action('save_post', [Relations\BindCity::class, 'sync'], 20, 3);

    // Admin niceties
    if (class_exists('\\HappyPlace\\Local\\Admin\\Columns')) {
        Admin\Columns::boot();
    }

    // Frontend: shortcode + assets + templates helper
    add_shortcode('hpl_this_weekend', [Frontend\ThisWeekendShortcode::class, 'render']);
    add_action('wp_enqueue_scripts', [Frontend\Assets::class, 'enqueue']);
    add_filter('template_include', [Frontend\Templates::class, 'maybe_templates'], 99);

    // Agent pipeline bootstrap
    Agent\Bootstrap::init();

    // ACF JSON paths
    $this->setup_acf_json_paths();

    // WP-CLI commands
    if (defined('WP_CLI') && WP_CLI) {
      \WP_CLI::add_command('hpl:cities', CLI\CitiesFetch::class);
      \WP_CLI::add_command('hpl:agent', Agent\CLI::class);
      \WP_CLI::add_command('hpl:ingest', Agent\CLIIngest::class);
      \WP_CLI::add_command('hpl:places', Agent\CLIPlaces::class);
    }
  }

  private function setup_acf_json_paths() {
    add_filter('acf/settings/load_json', function($paths) {
      $paths[] = HPL_PATH . 'acf-json';
      return $paths;
    });
  }

  /**
   * Setup configuration with constant overrides for secrets
   */
  private function setup_config_overrides() {
    add_filter('option_hpl_config', function($config) {
      if (!is_array($config)) $config = [];
      
      // Override API keys from constants if defined
      if (defined('HPL_GOOGLE_PLACES_KEY')) {
        $config['google_places_api_key'] = HPL_GOOGLE_PLACES_KEY;
      }
      
      if (defined('HPL_OPENAI_API_KEY')) {
        $config['openai_api_key'] = HPL_OPENAI_API_KEY;
      }
      
      if (defined('HPL_ANTHROPIC_API_KEY')) {
        $config['anthropic_api_key'] = HPL_ANTHROPIC_API_KEY;
      }
      
      return $config;
    });
  }
}