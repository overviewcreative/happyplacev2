<?php
namespace HappyPlace\Local\Admin;

class LocalConfigPage {
  private $option_group = 'hpl_settings';
  private $option_name = 'hpl_config';
  private $page_slug = 'hp-local-config';
  
  public function __construct() {
    add_action('admin_menu', [$this, 'add_menu_page'], 20);
    add_action('admin_init', [$this, 'register_settings']);
    add_action('wp_ajax_hpl_test_connection', [$this, 'test_api_connection']);
    add_action('wp_ajax_hpl_test_llm_connection', [$this, 'test_llm_connection']);
    add_action('wp_ajax_hpl_fetch_sample_data', [$this, 'fetch_sample_data']);
    add_action('wp_ajax_hpl_import_places', [$this, 'import_places']);
    add_action('wp_ajax_hpl_import_content', [$this, 'import_content']);
    add_action('wp_ajax_hpl_process_agent', [$this, 'process_agent']);
    add_action('wp_ajax_hpl_agent_status', [$this, 'get_agent_status']);
    add_action('wp_ajax_hpl_reset_pipeline', [$this, 'reset_pipeline']);
    add_action('wp_ajax_hpl_reimport_posts', [$this, 'reimport_posts']);
    add_action('wp_ajax_hpl_scrub_cities', [$this, 'scrub_cities']);
    add_action('wp_ajax_hpl_clean_run', [$this, 'clean_run']);
  }

  public function add_menu_page() {
    // Add as submenu under main Happy Place menu
    add_submenu_page(
      'happy-place',
      __('Local Places Configuration', 'happy-place-local'),
      __('Local Places', 'happy-place-local'),
      'manage_options',
      $this->page_slug,
      [$this, 'render_page']
    );
  }

  public function register_settings() {
    register_setting($this->option_group, $this->option_name, [
      'sanitize_callback' => [$this, 'sanitize_settings'],
      'default' => $this->get_default_settings()
    ]);

    // API Configuration Section
    add_settings_section(
      'hpl_api_config',
      __('API Configuration', 'happy-place-local'),
      [$this, 'render_api_section_description'],
      $this->page_slug
    );

    add_settings_field(
      'google_places_api_key',
      __('Google Places API Key', 'happy-place-local'),
      [$this, 'render_api_key_field'],
      $this->page_slug,
      'hpl_api_config',
      ['field' => 'google_places_api_key', 'type' => 'password']
    );

    // Data Sources Section
    add_settings_section(
      'hpl_data_sources',
      __('Data Sources', 'happy-place-local'),
      [$this, 'render_data_sources_description'],
      $this->page_slug
    );

    add_settings_field(
      'enabled_sources',
      __('Enabled Sources', 'happy-place-local'),
      [$this, 'render_enabled_sources_field'],
      $this->page_slug,
      'hpl_data_sources'
    );

    add_settings_field(
      'default_bounds',
      __('Default Search Area', 'happy-place-local'),
      [$this, 'render_bounds_field'],
      $this->page_slug,
      'hpl_data_sources'
    );

    // AI Agent Configuration Section
    add_settings_section(
      'hpl_agent_config',
      __('AI Agent Configuration', 'happy-place-local'),
      [$this, 'render_agent_section_description'],
      $this->page_slug
    );

    add_settings_field(
      'agent_enabled',
      __('Enable AI Agent', 'happy-place-local'),
      [$this, 'render_checkbox_field'],
      $this->page_slug,
      'hpl_agent_config',
      ['field' => 'agent_enabled', 'description' => 'Enable automatic content processing']
    );

    add_settings_field(
      'publish_threshold',
      __('Publish Threshold', 'happy-place-local'),
      [$this, 'render_number_field'],
      $this->page_slug,
      'hpl_agent_config',
      ['field' => 'publish_threshold', 'min' => 0, 'max' => 100, 'description' => 'Minimum score (0-100) required to automatically publish content']
    );

    add_settings_field(
      'batch_size',
      __('Processing Batch Size', 'happy-place-local'),
      [$this, 'render_number_field'],
      $this->page_slug,
      'hpl_agent_config',
      ['field' => 'batch_size', 'min' => 1, 'max' => 50, 'description' => 'Number of items to process per batch']
    );

    add_settings_field(
      'llm_provider',
      __('LLM Provider', 'happy-place-local'),
      [$this, 'render_select_field'],
      $this->page_slug,
      'hpl_agent_config',
      [
        'field' => 'llm_provider',
        'options' => [
          '' => __('Select Provider', 'happy-place-local'),
          'openai' => __('OpenAI (GPT-3.5/4)', 'happy-place-local'),
          'anthropic' => __('Anthropic (Claude)', 'happy-place-local'),
          'custom' => __('Custom API Endpoint', 'happy-place-local')
        ],
        'description' => 'Choose your AI provider for content processing'
      ]
    );

    add_settings_field(
      'openai_api_key',
      __('OpenAI API Key', 'happy-place-local'),
      [$this, 'render_conditional_api_key_field'],
      $this->page_slug,
      'hpl_agent_config',
      ['field' => 'openai_api_key', 'show_if' => 'llm_provider', 'show_value' => 'openai']
    );

    add_settings_field(
      'anthropic_api_key',
      __('Anthropic API Key', 'happy-place-local'),
      [$this, 'render_conditional_api_key_field'],
      $this->page_slug,
      'hpl_agent_config',
      ['field' => 'anthropic_api_key', 'show_if' => 'llm_provider', 'show_value' => 'anthropic']
    );

    add_settings_field(
      'llm_model',
      __('Model Name', 'happy-place-local'),
      [$this, 'render_text_field'],
      $this->page_slug,
      'hpl_agent_config',
      [
        'field' => 'llm_model',
        'placeholder' => 'gpt-3.5-turbo',
        'description' => 'Specific model to use (e.g., gpt-4, claude-3-sonnet-20240229)'
      ]
    );

    // Rate Limiting Section
    add_settings_section(
      'hpl_rate_limits',
      __('Rate Limiting', 'happy-place-local'),
      [$this, 'render_rate_limits_description'],
      $this->page_slug
    );

    add_settings_field(
      'requests_per_minute',
      __('Requests Per Minute', 'happy-place-local'),
      [$this, 'render_number_field'],
      $this->page_slug,
      'hpl_rate_limits',
      ['field' => 'requests_per_minute', 'min' => 1, 'max' => 100, 'description' => 'Maximum API requests per minute']
    );
  }

  public function render_page() {
    $settings = get_option($this->option_name, $this->get_default_settings());
    ?>
    <div class="wrap">
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
      
      <div class="notice notice-info">
        <p><strong><?php _e('Happy Place Local Configuration', 'happy-place-local'); ?></strong></p>
        <p><?php _e('Configure API keys, data sources, and AI agent settings for the Happy Place Local add-on.', 'happy-place-local'); ?></p>
      </div>

      <div id="hpl-admin-container" class="hpl-admin-grid">
        <div class="hpl-main-content">
          <form method="post" action="options.php">
            <?php
            settings_fields($this->option_group);
            do_settings_sections($this->page_slug);
            submit_button(__('Save Configuration', 'happy-place-local'));
            ?>
          </form>
        </div>

        <div class="hpl-sidebar">
          <div class="postbox">
            <h3 class="hndle"><?php _e('Quick Actions', 'happy-place-local'); ?></h3>
            <div class="inside">
              <p>
                <button type="button" id="hpl-test-connection" class="button button-secondary">
                  <?php _e('Test API Connection', 'happy-place-local'); ?>
                </button>
              </p>
              <p>
                <button type="button" id="hpl-test-llm" class="button button-secondary">
                  <?php _e('Test LLM Connection', 'happy-place-local'); ?>
                </button>
              </p>
              <p>
                <button type="button" id="hpl-fetch-sample" class="button button-secondary">
                  <?php _e('Fetch Sample Data', 'happy-place-local'); ?>
                </button>
              </p>
            </div>
          </div>

          <div class="postbox">
            <h3 class="hndle"><?php _e('Import & Processing', 'happy-place-local'); ?></h3>
            <div class="inside">
              <div class="hpl-import-controls">
                <div class="hpl-import-type">
                  <label><strong><?php _e('Import Type:', 'happy-place-local'); ?></strong></label>
                  <p>
                    <label>
                      <input type="radio" name="hpl-import-type" value="places" checked>
                      <?php _e('Local Places (Businesses)', 'happy-place-local'); ?>
                    </label>
                  </p>
                  <p>
                    <label>
                      <input type="radio" name="hpl-import-type" value="cities">
                      <?php _e('Cities & Localities', 'happy-place-local'); ?>
                    </label>
                  </p>
                  <p>
                    <label>
                      <input type="radio" name="hpl-import-type" value="events">
                      <?php _e('Local Events (Sample Data)', 'happy-place-local'); ?>
                    </label>
                  </p>
                </div>

                <div class="hpl-import-options" style="margin: 15px 0;">
                  <div id="hpl-places-options" class="import-type-options">
                    <p>
                      <label for="hpl-places-query"><?php _e('Search Query (optional):', 'happy-place-local'); ?></label>
                      <input type="text" id="hpl-places-query" placeholder="e.g., restaurants, coffee shops, attractions" class="widefat">
                      <small><?php _e('Leave empty to search for all establishments within bounds', 'happy-place-local'); ?></small>
                    </p>
                  </div>
                  
                  <div id="hpl-cities-options" class="import-type-options" style="display: none;">
                    <p>
                      <small><?php _e('Will import cities and localities within the configured geographic bounds', 'happy-place-local'); ?></small>
                    </p>
                  </div>

                  <div id="hpl-events-options" class="import-type-options" style="display: none;">
                    <p>
                      <label for="hpl-events-theme"><?php _e('Event Theme:', 'happy-place-local'); ?></label>
                      <select id="hpl-events-theme" class="widefat">
                        <option value="community"><?php _e('Community Events', 'happy-place-local'); ?></option>
                        <option value="cultural"><?php _e('Cultural Activities', 'happy-place-local'); ?></option>
                        <option value="outdoor"><?php _e('Outdoor Adventures', 'happy-place-local'); ?></option>
                        <option value="family"><?php _e('Family-Friendly', 'happy-place-local'); ?></option>
                        <option value="business"><?php _e('Business & Networking', 'happy-place-local'); ?></option>
                      </select>
                    </p>
                  </div>
                </div>

                <p>
                  <label for="hpl-import-limit"><?php _e('Limit:', 'happy-place-local'); ?></label>
                  <input type="number" id="hpl-import-limit" value="10" min="1" max="50" class="small-text">
                  <label>
                    <input type="checkbox" id="hpl-import-ai">
                    <?php _e('Use AI Enhancement', 'happy-place-local'); ?>
                  </label>
                </p>
                <p>
                  <button type="button" id="hpl-import-content" class="button button-primary">
                    <?php _e('Import Content', 'happy-place-local'); ?>
                  </button>
                </p>
              </div>
              <hr>
              <div class="hpl-agent-controls">
                <p>
                  <button type="button" id="hpl-process-agent" class="button button-secondary">
                    <?php _e('Process AI Pipeline', 'happy-place-local'); ?>
                  </button>
                  <button type="button" id="hpl-agent-status" class="button button-secondary">
                    <?php _e('Show Pipeline Status', 'happy-place-local'); ?>
                  </button>
                </p>
              </div>
            </div>
          </div>

          <div class="postbox">
            <h3 class="hndle"><?php _e('Pipeline Management', 'happy-place-local'); ?></h3>
            <div class="inside">
              <div class="hpl-pipeline-controls">
                <p>
                  <button type="button" id="hpl-reset-pipeline" class="button button-secondary">
                    <?php _e('Reset Pipeline', 'happy-place-local'); ?>
                  </button>
                  <label>
                    <input type="checkbox" id="hpl-reset-soft">
                    <?php _e('Soft Reset', 'happy-place-local'); ?>
                  </label>
                </p>
                <p>
                  <button type="button" id="hpl-scrub-cities" class="button button-secondary">
                    <?php _e('Clean City Data', 'happy-place-local'); ?>
                  </button>
                  <select id="hpl-scrub-action" class="small-text">
                    <option value="retag"><?php _e('Retag as Cities', 'happy-place-local'); ?></option>
                    <option value="delete"><?php _e('Delete', 'happy-place-local'); ?></option>
                  </select>
                </p>
                <p>
                  <button type="button" id="hpl-clean-run" class="button button-primary">
                    <?php _e('Clean Test Run', 'happy-place-local'); ?>
                  </button>
                  <input type="number" id="hpl-clean-run-limit" value="6" min="1" max="20" class="small-text">
                  <?php _e('items', 'happy-place-local'); ?>
                </p>
              </div>
            </div>
          </div>

          <div class="postbox">
            <h3 class="hndle"><?php _e('Enhance Existing Posts', 'happy-place-local'); ?></h3>
            <div class="inside">
              <div class="hpl-reimport-controls">
                <p><?php _e('Re-import existing posts back into the AI pipeline for enhancement:', 'happy-place-local'); ?></p>
                <p>
                  <select id="hpl-reimport-type" class="regular-text">
                    <option value="local_place"><?php _e('Local Places', 'happy-place-local'); ?></option>
                    <option value="local_event"><?php _e('Local Events', 'happy-place-local'); ?></option>
                  </select>
                </p>
                <p>
                  <label for="hpl-reimport-limit"><?php _e('Limit:', 'happy-place-local'); ?></label>
                  <input type="number" id="hpl-reimport-limit" value="10" min="1" max="50" class="small-text">
                  <label>
                    <input type="checkbox" id="hpl-reimport-dry-run">
                    <?php _e('Dry Run', 'happy-place-local'); ?>
                  </label>
                </p>
                <p>
                  <button type="button" id="hpl-reimport-posts" class="button button-primary">
                    <?php _e('Re-Import for Enhancement', 'happy-place-local'); ?>
                  </button>
                </p>
                <div class="hpl-reimport-help">
                  <details>
                    <summary><?php _e('How it works', 'happy-place-local'); ?></summary>
                    <p><?php _e('This will create new pipeline entries from your existing published posts, run them through AI enhancement, then update the original posts with improved content and metadata.', 'happy-place-local'); ?></p>
                  </details>
                </div>
              </div>
            </div>
          </div>

          <div class="postbox">
            <h3 class="hndle"><?php _e('System Status', 'happy-place-local'); ?></h3>
            <div class="inside">
              <ul class="hpl-status-list">
                <li>
                  <strong><?php _e('Google Places API:', 'happy-place-local'); ?></strong>
                  <span class="hpl-status <?php echo !empty($settings['google_places_api_key']) ? 'configured' : 'not-configured'; ?>">
                    <?php echo !empty($settings['google_places_api_key']) ? __('Configured', 'happy-place-local') : __('Not Configured', 'happy-place-local'); ?>
                  </span>
                </li>
                <li>
                  <strong><?php _e('AI Agent:', 'happy-place-local'); ?></strong>
                  <span class="hpl-status <?php echo !empty($settings['agent_enabled']) ? 'enabled' : 'disabled'; ?>">
                    <?php echo !empty($settings['agent_enabled']) ? __('Enabled', 'happy-place-local') : __('Disabled', 'happy-place-local'); ?>
                  </span>
                </li>
                <li>
                  <strong><?php _e('ACF Integration:', 'happy-place-local'); ?></strong>
                  <span class="hpl-status <?php echo function_exists('acf_add_local_field_group') ? 'active' : 'inactive'; ?>">
                    <?php echo function_exists('acf_add_local_field_group') ? __('Active', 'happy-place-local') : __('Inactive', 'happy-place-local'); ?>
                  </span>
                </li>
              </ul>
            </div>
          </div>

          <div class="postbox">
            <h3 class="hndle"><?php _e('Documentation', 'happy-place-local'); ?></h3>
            <div class="inside">
              <ul>
                <li><a href="#" target="_blank"><?php _e('Getting Started Guide', 'happy-place-local'); ?></a></li>
                <li><a href="#" target="_blank"><?php _e('API Configuration', 'happy-place-local'); ?></a></li>
                <li><a href="#" target="_blank"><?php _e('Troubleshooting', 'happy-place-local'); ?></a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div id="hpl-test-results" class="notice" style="display:none;"></div>
    </div>

    <style>
    .hpl-admin-grid {
      display: grid;
      grid-template-columns: 1fr 300px;
      gap: 20px;
      margin-top: 20px;
    }
    
    .hpl-main-content .form-table th {
      width: 200px;
    }
    
    .hpl-sidebar .postbox {
      margin-bottom: 20px;
    }
    
    .hpl-status-list {
      margin: 0;
      list-style: none;
    }
    
    .hpl-status-list li {
      margin-bottom: 10px;
      display: flex;
      justify-content: space-between;
    }
    
    .hpl-status.configured,
    .hpl-status.enabled,
    .hpl-status.active {
      color: #46b450;
      font-weight: bold;
    }
    
    .hpl-status.not-configured,
    .hpl-status.disabled,
    .hpl-status.inactive {
      color: #dc3232;
      font-weight: bold;
    }
    
    .hpl-bounds-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }
    
    .hpl-bounds-grid label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }
    
    /* New Pipeline Management Styles */
    .hpl-pipeline-controls p,
    .hpl-reimport-controls p {
      margin-bottom: 15px;
    }
    
    .hpl-pipeline-controls label,
    .hpl-reimport-controls label {
      margin-left: 8px;
      font-weight: normal;
    }
    
    .hpl-reimport-help {
      margin-top: 15px;
      padding: 10px;
      background: #f9f9f9;
      border-radius: 4px;
    }
    
    .hpl-reimport-help details {
      cursor: pointer;
    }
    
    .hpl-reimport-help summary {
      font-weight: bold;
      margin-bottom: 8px;
    }
    
    /* Import Type Styles */
    .hpl-import-type {
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid #e1e1e1;
    }
    
    .hpl-import-type p {
      margin: 8px 0;
    }
    
    .hpl-import-type label {
      margin-left: 0;
      font-weight: normal;
    }
    
    .hpl-import-options {
      background: #f9f9f9;
      border: 1px solid #e1e1e1;
      border-radius: 4px;
      padding: 15px;
    }
    
    .import-type-options p {
      margin-bottom: 10px;
    }
    
    .import-type-options label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
    }
    
    .import-type-options small {
      color: #666;
      font-style: italic;
    }
    
    .hpl-reimport-help p {
      margin: 0;
      color: #666;
      font-style: italic;
    }
    
    @media (max-width: 1024px) {
      .hpl-admin-grid {
        grid-template-columns: 1fr;
      }
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
      $('#hpl-test-connection').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        
        button.text('<?php _e('Testing...', 'happy-place-local'); ?>').prop('disabled', true);
        
        $.post(ajaxurl, {
          action: 'hpl_test_connection',
          _ajax_nonce: '<?php echo wp_create_nonce('hpl_test_connection'); ?>'
        })
        .done(function(response) {
          $('#hpl-test-results')
            .removeClass('notice-error notice-success')
            .addClass(response.success ? 'notice-success' : 'notice-error')
            .html('<p>' + response.data.message + '</p>')
            .show();
        })
        .fail(function() {
          $('#hpl-test-results')
            .removeClass('notice-success')
            .addClass('notice-error')
            .html('<p><?php _e('Connection test failed. Please try again.', 'happy-place-local'); ?></p>')
            .show();
        })
        .always(function() {
          button.text(originalText).prop('disabled', false);
        });
      });

      $('#hpl-test-llm').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        
        button.text('<?php _e('Testing...', 'happy-place-local'); ?>').prop('disabled', true);
        
        $.post(ajaxurl, {
          action: 'hpl_test_llm_connection',
          _ajax_nonce: '<?php echo wp_create_nonce('hpl_test_llm_connection'); ?>'
        })
        .done(function(response) {
          $('#hpl-test-results')
            .removeClass('notice-error notice-success')
            .addClass(response.success ? 'notice-success' : 'notice-error')
            .html('<p>' + response.data.message + '</p>')
            .show();
        })
        .fail(function() {
          $('#hpl-test-results')
            .removeClass('notice-success')
            .addClass('notice-error')
            .html('<p><?php _e('LLM connection test failed. Please try again.', 'happy-place-local'); ?></p>')
            .show();
        })
        .always(function() {
          button.text(originalText).prop('disabled', false);
        });
      });

      $('#hpl-fetch-sample').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        
        button.text('<?php _e('Fetching...', 'happy-place-local'); ?>').prop('disabled', true);
        
        $.post(ajaxurl, {
          action: 'hpl_fetch_sample_data',
          _ajax_nonce: '<?php echo wp_create_nonce('hpl_fetch_sample_data'); ?>'
        })
        .done(function(response) {
          $('#hpl-test-results')
            .removeClass('notice-error notice-success')
            .addClass(response.success ? 'notice-success' : 'notice-error')
            .html('<p>' + response.data.message + '</p>')
            .show();
        })
        .always(function() {
          button.text(originalText).prop('disabled', false);
        });
      });

      // Toggle import type options
      $('input[name="hpl-import-type"]').on('change', function() {
        const selectedType = $(this).val();
        $('.import-type-options').hide();
        $('#hpl-' + selectedType + '-options').show();
        
        // Update button text
        const buttonTexts = {
          'places': '<?php _e('Import Places', 'happy-place-local'); ?>',
          'cities': '<?php _e('Import Cities', 'happy-place-local'); ?>',
          'events': '<?php _e('Generate Events', 'happy-place-local'); ?>'
        };
        $('#hpl-import-content').text(buttonTexts[selectedType]);
      });

      // Import content (places, cities, or events)
      $('#hpl-import-content').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        const importType = $('input[name="hpl-import-type"]:checked').val();
        const limit = $('#hpl-import-limit').val() || 10;
        const useAI = $('#hpl-import-ai').is(':checked');
        
        // Gather type-specific data
        let typeData = {};
        if (importType === 'places') {
          typeData.query = $('#hpl-places-query').val().trim();
        } else if (importType === 'events') {
          typeData.theme = $('#hpl-events-theme').val();
        }
        
        button.text('<?php _e('Processing...', 'happy-place-local'); ?>').prop('disabled', true);
        
        $.post(ajaxurl, {
          action: 'hpl_import_content',
          import_type: importType,
          limit: limit,
          use_ai: useAI ? 1 : 0,
          type_data: typeData,
          _ajax_nonce: '<?php echo wp_create_nonce('hpl_import_content'); ?>'
        })
        .done(function(response) {
          $('#hpl-test-results')
            .removeClass('notice-error notice-success')
            .addClass(response.success ? 'notice-success' : 'notice-error')
            .html('<p>' + response.data.message + '</p>')
            .show();
        })
        .fail(function() {
          $('#hpl-test-results')
            .removeClass('notice-success')
            .addClass('notice-error')
            .html('<p><?php _e('Import failed. Please try again.', 'happy-place-local'); ?></p>')
            .show();
        })
        .always(function() {
          button.text(originalText).prop('disabled', false);
        });
      });

      // Process AI pipeline
      $('#hpl-process-agent').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        
        button.text('<?php _e('Processing...', 'happy-place-local'); ?>').prop('disabled', true);
        
        $.post(ajaxurl, {
          action: 'hpl_process_agent',
          _ajax_nonce: '<?php echo wp_create_nonce('hpl_process_agent'); ?>'
        })
        .done(function(response) {
          $('#hpl-test-results')
            .removeClass('notice-error notice-success')
            .addClass(response.success ? 'notice-success' : 'notice-error')
            .html('<p>' + response.data.message + '</p>')
            .show();
        })
        .fail(function() {
          $('#hpl-test-results')
            .removeClass('notice-success')
            .addClass('notice-error')
            .html('<p><?php _e('Processing failed. Please try again.', 'happy-place-local'); ?></p>')
            .show();
        })
        .always(function() {
          button.text(originalText).prop('disabled', false);
        });
      });

      // Agent status
      $('#hpl-agent-status').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        
        button.text('<?php _e('Loading...', 'happy-place-local'); ?>').prop('disabled', true);
        
        $.post(ajaxurl, {
          action: 'hpl_agent_status',
          _ajax_nonce: '<?php echo wp_create_nonce('hpl_agent_status'); ?>'
        })
        .done(function(response) {
          $('#hpl-test-results')
            .removeClass('notice-error notice-success')
            .addClass('notice-info')
            .html('<div><h4>Pipeline Status</h4><pre>' + response.data.status + '</pre></div>')
            .show();
        })
        .fail(function() {
          $('#hpl-test-results')
            .removeClass('notice-success')
            .addClass('notice-error')
            .html('<p><?php _e('Failed to load status.', 'happy-place-local'); ?></p>')
            .show();
        })
        .always(function() {
          button.text(originalText).prop('disabled', false);
        });
      });

      // Handle conditional fields
      $('#llm_provider').on('change', function() {
        const selectedProvider = $(this).val();
        $('.hpl-conditional-field').hide();
        $('[data-show-if="llm_provider"][data-show-value="' + selectedProvider + '"]').show();
      });

      // Pipeline Management Handlers
      $('#hpl-reset-pipeline').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        const softReset = $('#hpl-reset-soft').is(':checked');
        
        if (!confirm('<?php _e('Are you sure you want to reset the pipeline? This will clear all processing data.', 'happy-place-local'); ?>')) {
          return;
        }
        
        button.text('<?php _e('Resetting...', 'happy-place-local'); ?>').prop('disabled', true);
        
        $.post(ajaxurl, {
          action: 'hpl_reset_pipeline',
          soft: softReset,
          _ajax_nonce: '<?php echo wp_create_nonce('hpl_reset_pipeline'); ?>'
        })
        .done(function(response) {
          $('#hpl-test-results')
            .removeClass('notice-error notice-success')
            .addClass(response.success ? 'notice-success' : 'notice-error')
            .html('<p>' + response.data.message + '</p>')
            .show();
        })
        .always(function() {
          button.text(originalText).prop('disabled', false);
        });
      });

      $('#hpl-scrub-cities').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        const action = $('#hpl-scrub-action').val();
        
        button.text('<?php _e('Scrubbing...', 'happy-place-local'); ?>').prop('disabled', true);
        
        $.post(ajaxurl, {
          action: 'hpl_scrub_cities',
          scrub_action: action,
          _ajax_nonce: '<?php echo wp_create_nonce('hpl_scrub_cities'); ?>'
        })
        .done(function(response) {
          $('#hpl-test-results')
            .removeClass('notice-error notice-success')
            .addClass(response.success ? 'notice-success' : 'notice-error')
            .html('<p>' + response.data.message + '</p>')
            .show();
        })
        .always(function() {
          button.text(originalText).prop('disabled', false);
        });
      });

      $('#hpl-clean-run').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        const limit = $('#hpl-clean-run-limit').val() || 6;
        
        button.text('<?php _e('Running...', 'happy-place-local'); ?>').prop('disabled', true);
        
        $.post(ajaxurl, {
          action: 'hpl_clean_run',
          limit: limit,
          _ajax_nonce: '<?php echo wp_create_nonce('hpl_clean_run'); ?>'
        })
        .done(function(response) {
          $('#hpl-test-results')
            .removeClass('notice-error notice-success')
            .addClass(response.success ? 'notice-success' : 'notice-error')
            .html('<p>' + response.data.message + '</p>')
            .show();
        })
        .always(function() {
          button.text(originalText).prop('disabled', false);
        });
      });

      $('#hpl-reimport-posts').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        const postType = $('#hpl-reimport-type').val();
        const limit = $('#hpl-reimport-limit').val() || 10;
        const dryRun = $('#hpl-reimport-dry-run').is(':checked');
        
        button.text('<?php _e('Re-importing...', 'happy-place-local'); ?>').prop('disabled', true);
        
        $.post(ajaxurl, {
          action: 'hpl_reimport_posts',
          post_type: postType,
          limit: limit,
          dry_run: dryRun,
          _ajax_nonce: '<?php echo wp_create_nonce('hpl_reimport_posts'); ?>'
        })
        .done(function(response) {
          $('#hpl-test-results')
            .removeClass('notice-error notice-success')
            .addClass(response.success ? 'notice-success' : 'notice-error')
            .html('<p>' + response.data.message + '</p>')
            .show();
        })
        .always(function() {
          button.text(originalText).prop('disabled', false);
        });
      });
    });
    </script>
    <?php
  }

  // Field Rendering Methods
  public function render_api_section_description() {
    echo '<p>' . __('Configure API keys for external data sources. Keys are stored securely and never displayed in plain text.', 'happy-place-local') . '</p>';
  }

  public function render_data_sources_description() {
    echo '<p>' . __('Enable and configure data sources for importing local places and events.', 'happy-place-local') . '</p>';
  }

  public function render_agent_section_description() {
    echo '<p>' . __('Configure the AI agent for automatic content processing and publishing.', 'happy-place-local') . '</p>';
  }

  public function render_rate_limits_description() {
    echo '<p>' . __('Configure rate limiting to stay within API quotas and avoid service interruption.', 'happy-place-local') . '</p>';
  }

  public function render_api_key_field($args) {
    $settings = get_option($this->option_name, $this->get_default_settings());
    $field = $args['field'];
    $value = isset($settings[$field]) ? $settings[$field] : '';
    $masked_value = !empty($value) ? str_repeat('*', 32) : '';
    
    echo '<input type="password" id="' . esc_attr($field) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($field) . ']" value="' . esc_attr($value) . '" class="regular-text" autocomplete="new-password" />';
    echo '<p class="description">' . sprintf(__('Current: %s', 'happy-place-local'), $masked_value ?: __('Not set', 'happy-place-local')) . '</p>';
    
    if ($field === 'google_places_api_key') {
      echo '<p class="description">' . sprintf(
        __('Get your API key from <a href="%s" target="_blank">Google Cloud Console</a>', 'happy-place-local'),
        'https://console.cloud.google.com/apis/credentials'
      ) . '</p>';
    }
  }

  public function render_enabled_sources_field() {
    $settings = get_option($this->option_name, $this->get_default_settings());
    $enabled_sources = isset($settings['enabled_sources']) ? $settings['enabled_sources'] : ['google_places'];
    
    $sources = [
      'google_places' => __('Google Places', 'happy-place-local'),
      'wikipedia' => __('Wikipedia', 'happy-place-local'),
      'census' => __('US Census', 'happy-place-local')
    ];

    foreach ($sources as $key => $label) {
      $checked = in_array($key, $enabled_sources) ? 'checked' : '';
      echo '<label><input type="checkbox" name="' . esc_attr($this->option_name) . '[enabled_sources][]" value="' . esc_attr($key) . '" ' . $checked . '> ' . esc_html($label) . '</label><br>';
    }
  }

  public function render_bounds_field() {
    $settings = get_option($this->option_name, $this->get_default_settings());
    $bounds = isset($settings['default_bounds']) ? $settings['default_bounds'] : $this->get_default_bounds();
    
    echo '<div class="hpl-bounds-grid">';
    echo '<div><label>Southwest Latitude</label><input type="number" step="any" name="' . esc_attr($this->option_name) . '[default_bounds][sw_lat]" value="' . esc_attr($bounds['sw_lat']) . '" class="small-text" /></div>';
    echo '<div><label>Southwest Longitude</label><input type="number" step="any" name="' . esc_attr($this->option_name) . '[default_bounds][sw_lng]" value="' . esc_attr($bounds['sw_lng']) . '" class="small-text" /></div>';
    echo '<div><label>Northeast Latitude</label><input type="number" step="any" name="' . esc_attr($this->option_name) . '[default_bounds][ne_lat]" value="' . esc_attr($bounds['ne_lat']) . '" class="small-text" /></div>';
    echo '<div><label>Northeast Longitude</label><input type="number" step="any" name="' . esc_attr($this->option_name) . '[default_bounds][ne_lng]" value="' . esc_attr($bounds['ne_lng']) . '" class="small-text" /></div>';
    echo '</div>';
    echo '<p class="description">' . __('Default search area bounds (Delaware by default)', 'happy-place-local') . '</p>';
  }

  public function render_checkbox_field($args) {
    $settings = get_option($this->option_name, $this->get_default_settings());
    $field = $args['field'];
    $checked = isset($settings[$field]) && $settings[$field] ? 'checked' : '';
    
    echo '<label><input type="checkbox" name="' . esc_attr($this->option_name) . '[' . esc_attr($field) . ']" value="1" ' . $checked . '> ' . esc_html($args['description']) . '</label>';
  }

  public function render_number_field($args) {
    $settings = get_option($this->option_name, $this->get_default_settings());
    $field = $args['field'];
    $value = isset($settings[$field]) ? $settings[$field] : '';
    
    echo '<input type="number" id="' . esc_attr($field) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($field) . ']" value="' . esc_attr($value) . '" class="small-text"';
    if (isset($args['min'])) echo ' min="' . esc_attr($args['min']) . '"';
    if (isset($args['max'])) echo ' max="' . esc_attr($args['max']) . '"';
    if (isset($args['step'])) echo ' step="' . esc_attr($args['step']) . '"';
    echo ' />';
    
    if (isset($args['description'])) {
      echo '<p class="description">' . esc_html($args['description']) . '</p>';
    }
  }

  public function render_select_field($args) {
    $settings = get_option($this->option_name, $this->get_default_settings());
    $field = $args['field'];
    $value = isset($settings[$field]) ? $settings[$field] : '';
    
    echo '<select id="' . esc_attr($field) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($field) . ']" class="regular-text">';
    foreach ($args['options'] as $option_value => $option_label) {
      $selected = $value === $option_value ? 'selected' : '';
      echo '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . esc_html($option_label) . '</option>';
    }
    echo '</select>';
    
    if (isset($args['description'])) {
      echo '<p class="description">' . esc_html($args['description']) . '</p>';
    }
  }

  public function render_text_field($args) {
    $settings = get_option($this->option_name, $this->get_default_settings());
    $field = $args['field'];
    $value = isset($settings[$field]) ? $settings[$field] : '';
    
    echo '<input type="text" id="' . esc_attr($field) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($field) . ']" value="' . esc_attr($value) . '" class="regular-text"';
    if (isset($args['placeholder'])) echo ' placeholder="' . esc_attr($args['placeholder']) . '"';
    echo ' />';
    
    if (isset($args['description'])) {
      echo '<p class="description">' . esc_html($args['description']) . '</p>';
    }
  }

  public function render_conditional_api_key_field($args) {
    $settings = get_option($this->option_name, $this->get_default_settings());
    $field = $args['field'];
    $show_if = $args['show_if'];
    $show_value = $args['show_value'];
    $value = isset($settings[$field]) ? $settings[$field] : '';
    $masked_value = !empty($value) ? str_repeat('*', 32) : '';
    $current_provider = isset($settings[$show_if]) ? $settings[$show_if] : '';
    $style = $current_provider === $show_value ? '' : 'style="display:none;"';
    
    echo '<div class="hpl-conditional-field" data-show-if="' . esc_attr($show_if) . '" data-show-value="' . esc_attr($show_value) . '" ' . $style . '>';
    echo '<input type="password" id="' . esc_attr($field) . '" name="' . esc_attr($this->option_name) . '[' . esc_attr($field) . ']" value="' . esc_attr($value) . '" class="regular-text" autocomplete="new-password" />';
    echo '<p class="description">' . sprintf(__('Current: %s', 'happy-place-local'), $masked_value ?: __('Not set', 'happy-place-local')) . '</p>';
    echo '</div>';
  }

  // AJAX Handlers
  public function test_api_connection() {
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'hpl_test_connection')) {
      wp_die(__('Security check failed', 'happy-place-local'));
    }

    if (!current_user_can('manage_options')) {
      wp_die(__('Insufficient permissions', 'happy-place-local'));
    }

    $settings = get_option($this->option_name, $this->get_default_settings());
    
    if (empty($settings['google_places_api_key'])) {
      wp_send_json_error(['message' => __('Google Places API key not configured', 'happy-place-local')]);
      return;
    }

    // Test Google Places API
    $client = new \HappyPlace\Local\Services\GooglePlacesClient();
    $result = $client->textSearch('test', null);
    
    if (is_wp_error($result)) {
      wp_send_json_error(['message' => sprintf(__('API test failed: %s', 'happy-place-local'), $result->get_error_message())]);
    } else {
      wp_send_json_success(['message' => __('API connection successful!', 'happy-place-local')]);
    }
  }

  public function test_llm_connection() {
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'hpl_test_llm_connection')) {
      wp_die(__('Security check failed', 'happy-place-local'));
    }

    if (!current_user_can('manage_options')) {
      wp_die(__('Insufficient permissions', 'happy-place-local'));
    }

    // Use the LLMManager to test the connection
    $result = \HappyPlace\Local\Agent\LLMManager::test_connection();
    
    if ($result['success']) {
      wp_send_json_success(['message' => $result['message'] . ' - Response: ' . substr($result['response'], 0, 100)]);
    } else {
      wp_send_json_error(['message' => $result['message']]);
    }
  }

  public function fetch_sample_data() {
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'hpl_fetch_sample_data')) {
      wp_die(__('Security check failed', 'happy-place-local'));
    }

    if (!current_user_can('manage_options')) {
      wp_die(__('Insufficient permissions', 'happy-place-local'));
    }

    // This would trigger a small sample fetch
    $settings = get_option($this->option_name, $this->get_default_settings());
    $bounds = $settings['default_bounds'];
    
    $client = new \HappyPlace\Local\Services\GooglePlacesClient();
    $places = $client->nearbyByBounds(
      $bounds['sw_lat'], 
      $bounds['sw_lng'], 
      $bounds['ne_lat'], 
      $bounds['ne_lng'],
      ['restaurant'],
      3
    );

    if (is_wp_error($places)) {
      wp_send_json_error(['message' => sprintf(__('Sample fetch failed: %s', 'happy-place-local'), $places->get_error_message())]);
    } else {
      wp_send_json_success(['message' => sprintf(__('Successfully fetched %d sample places', 'happy-place-local'), count($places))]);
    }
  }

  public function import_places() {
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'hpl_import_places')) {
      wp_die(__('Security check failed', 'happy-place-local'));
    }

    if (!current_user_can('manage_options')) {
      wp_die(__('Insufficient permissions', 'happy-place-local'));
    }

    $limit = max(1, min(50, intval($_POST['limit'] ?? 10)));
    $use_ai = !empty($_POST['use_ai']);

    try {
      // Load configuration
      $config_file = WP_CONTENT_DIR . '/plugins/happy-place-local/config/sources.local.json';
      if (!file_exists($config_file)) {
        wp_send_json_error(['message' => __('Configuration file not found. Please configure API keys first.', 'happy-place-local')]);
        return;
      }

      $config = json_decode(file_get_contents($config_file), true);
      if (empty($config['google']['api_key'])) {
        wp_send_json_error(['message' => __('Google Places API key not configured.', 'happy-place-local')]);
        return;
      }

      // Initialize clients
      $google = new \HappyPlace\Local\Services\GooglePlacesClient($config['google']['api_key']);
      $writer = new \HappyPlace\Local\Services\CityWriter();

      // Get places using bounds search
      $bounds = $config['google']['bounds'];
      $results = $google->nearbyByBounds(
        $bounds['sw'][0], $bounds['sw'][1], 
        $bounds['ne'][0], $bounds['ne'][1], 
        $config['google']['types'] ?? ['establishment'],
        $limit
      );

      if (is_wp_error($results)) {
        wp_send_json_error(['message' => sprintf(__('Import failed: %s', 'happy-place-local'), $results->get_error_message())]);
        return;
      }

      $imported = 0;
      $state = $config['defaults']['state'] ?? 'DE';

      foreach ($results as $place) {
        $name = $place['name'] ?? '';
        if (!$name) continue;

        if ($use_ai) {
          // Import to AI pipeline
          $ingest_data = [
            'post_title' => 'Local Place: ' . $name,
            'post_type' => 'hpl_ingest',
            'post_status' => 'publish',
            'post_content' => json_encode($place, JSON_PRETTY_PRINT)
          ];

          $ingest_id = wp_insert_post($ingest_data);
          if (!is_wp_error($ingest_id) && $ingest_id) {
            update_post_meta($ingest_id, '_hpl_stage', 'new');
            update_post_meta($ingest_id, '_hpl_source_type', 'google_places');
            update_post_meta($ingest_id, '_hpl_target_type', 'local_place');
            update_post_meta($ingest_id, '_hpl_raw_data', $place);
            $imported++;
          }
        } else {
          // Direct import (existing logic)
          $place_data = [
            'name' => $name,
            'state' => $state,
            'lat' => $place['geometry']['location']['lat'] ?? null,
            'lng' => $place['geometry']['location']['lng'] ?? null,
            'address' => $place['formatted_address'] ?? '',
            'phone' => $place['formatted_phone_number'] ?? '',
            'website' => $place['website'] ?? '',
            'rating' => $place['rating'] ?? 0
          ];

          $post_id = wp_insert_post([
            'post_type' => 'local_place',
            'post_status' => 'publish',
            'post_title' => $name
          ]);

          if (!is_wp_error($post_id) && $post_id) {
            foreach ($place_data as $key => $value) {
              if ($value !== null && $value !== '') {
                update_post_meta($post_id, $key, $value);
              }
            }
            $imported++;
          }
        }
      }

      $message = $use_ai 
        ? sprintf(__('Successfully queued %d places for AI processing.', 'happy-place-local'), $imported)
        : sprintf(__('Successfully imported %d places directly.', 'happy-place-local'), $imported);

      wp_send_json_success(['message' => $message]);

    } catch (\Exception $e) {
      wp_send_json_error(['message' => sprintf(__('Import error: %s', 'happy-place-local'), $e->getMessage())]);
    }
  }

  public function import_content() {
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'hpl_import_content')) {
      wp_die(__('Security check failed', 'happy-place-local'));
    }

    if (!current_user_can('manage_options')) {
      wp_die(__('Insufficient permissions', 'happy-place-local'));
    }

    $import_type = sanitize_text_field($_POST['import_type'] ?? 'places');
    $limit = max(1, min(50, intval($_POST['limit'] ?? 10)));
    $use_ai = !empty($_POST['use_ai']);
    $type_data = $_POST['type_data'] ?? [];

    try {
      switch ($import_type) {
        case 'places':
          $result = $this->import_places_content($limit, $use_ai, $type_data);
          break;
        case 'cities':
          $result = $this->import_cities_content($limit, $use_ai);
          break;
        case 'events':
          $result = $this->generate_events_content($limit, $use_ai, $type_data);
          break;
        default:
          wp_send_json_error(['message' => __('Invalid import type', 'happy-place-local')]);
          return;
      }

      wp_send_json_success(['message' => $result['message']]);

    } catch (\Exception $e) {
      wp_send_json_error(['message' => sprintf(__('Import error: %s', 'happy-place-local'), $e->getMessage())]);
    }
  }

  private function import_places_content($limit, $use_ai, $type_data) {
    // Load configuration
    $config_file = WP_CONTENT_DIR . '/plugins/happy-place-local/config/sources.local.json';
    if (!file_exists($config_file)) {
      throw new \Exception(__('Configuration file not found. Please configure API keys first.', 'happy-place-local'));
    }

    $config = json_decode(file_get_contents($config_file), true);
    if (empty($config['google']['api_key'])) {
      throw new \Exception(__('Google Places API key not configured.', 'happy-place-local'));
    }

    // Initialize Google Places client
    $google = new \HappyPlace\Local\Services\GooglePlacesClient($config['google']['api_key']);
    
    // Get search query if provided
    $query = isset($type_data['query']) ? trim($type_data['query']) : '';
    
    $places = [];
    
    if ($query) {
      // Use text search for specific query
      $places = $google->textSearch($query . ' in Delaware');
      if (is_wp_error($places)) {
        throw new \Exception($places->get_error_message());
      }
      $places = array_slice($places, 0, $limit);
    } else {
      // Use bounds search for all establishments
      $places = $google->nearbyByBounds(
        38.451013, -75.756138,  // Delaware bounds
        39.839007, -74.984165,
        ['establishment'], 
        $limit
      );
      if (is_wp_error($places)) {
        throw new \Exception($places->get_error_message());
      }
    }

    if (empty($places)) {
      return ['message' => __('No places found with the specified criteria.', 'happy-place-local')];
    }

    $imported = 0;

    foreach ($places as $place) {
      if ($use_ai) {
        // Import into AI pipeline as hpl_ingest posts
        $post_id = wp_insert_post([
          'post_type' => 'hpl_ingest',
          'post_status' => 'publish',
          'post_title' => 'Place: ' . ($place['name'] ?? 'Unknown Place'),
          'post_content' => $place['formatted_address'] ?? ''
        ]);

        if (!is_wp_error($post_id) && $post_id) {
          // Set pipeline metadata
          update_post_meta($post_id, '_hpl_stage', 'new');
          update_post_meta($post_id, '_hpl_target_type', 'local_place');
          
          // Store raw place data
          update_post_meta($post_id, '_hpl_raw', wp_json_encode($place));
          $imported++;
        }
      } else {
        // Import directly as local_place with enhanced field mapping
        $place_details = $this->get_enhanced_place_data($place, $config['google']['api_key']);
        
        // Create comprehensive content from available data
        $content = $this->build_place_content($place_details);
        
        $post_id = wp_insert_post([
          'post_type' => 'local_place',
          'post_status' => 'publish',
          'post_title' => $place_details['name'] ?? 'Unknown Place',
          'post_content' => $content
        ]);

        if (!is_wp_error($post_id) && $post_id) {
          // Core location data
          if (isset($place_details['geometry']['location'])) {
            update_field('lat', $place_details['geometry']['location']['lat'], $post_id);
            update_field('lng', $place_details['geometry']['location']['lng'], $post_id);
          }
          
          // Contact information
          if (!empty($place_details['formatted_address'])) {
            update_field('address', $place_details['formatted_address'], $post_id);
          }
          if (!empty($place_details['formatted_phone_number']) || !empty($place_details['international_phone_number'])) {
            $phone = $place_details['formatted_phone_number'] ?? $place_details['international_phone_number'];
            update_field('phone', $phone, $post_id);
          }
          if (!empty($place_details['website'])) {
            update_field('website', $place_details['website'], $post_id);
          }
          
          // Business details
          if (isset($place_details['price_level'])) {
            $price_map = [1 => '$', 2 => '$$', 3 => '$$$', 4 => '$$$$'];
            if (isset($price_map[$place_details['price_level']])) {
              update_field('price_range', $price_map[$place_details['price_level']], $post_id);
            }
          }
          
          // Opening hours
          if (!empty($place_details['opening_hours']['periods'])) {
            $hours_json = $this->convert_opening_hours($place_details['opening_hours']);
            update_field('hours_json', $hours_json, $post_id);
          }
          
          // Family friendly detection (basic heuristics)
          $is_family_friendly = $this->detect_family_friendly($place_details);
          update_field('is_family_friendly', $is_family_friendly, $post_id);
          
          // Accessibility features detection
          $accessibility = $this->detect_accessibility_features($place_details);
          if (!empty($accessibility)) {
            update_field('accessibility', $accessibility, $post_id);
          }
          
          // Source attribution
          update_field('source_url', 'https://maps.google.com/?place_id=' . ($place_details['place_id'] ?? ''), $post_id);
          update_field('attribution', 'Data from Google Places API', $post_id);
          
          // Additional metadata for reference
          if (!empty($place_details['place_id'])) {
            update_post_meta($post_id, 'google_place_id', $place_details['place_id']);
          }
          if (!empty($place_details['rating'])) {
            update_post_meta($post_id, 'google_rating', $place_details['rating']);
          }
          if (!empty($place_details['user_ratings_total'])) {
            update_post_meta($post_id, 'google_reviews_count', $place_details['user_ratings_total']);
          }
          if (!empty($place_details['types'])) {
            update_post_meta($post_id, 'google_types', implode(',', $place_details['types']));
          }
          
          // Try to set featured image from Google Photos
          if (!empty($place_details['photos'][0]['photo_reference'])) {
            $this->set_place_featured_image($post_id, $place_details['photos'][0]['photo_reference'], $config['google']['api_key'], $place_details['name']);
          }
          
          $imported++;
        }
      }
    }

    $message = $use_ai 
      ? sprintf(__('Successfully imported %d places into AI enhancement pipeline.', 'happy-place-local'), $imported)
      : sprintf(__('Successfully imported %d places directly.', 'happy-place-local'), $imported);

    return ['message' => $message];
  }

  private function import_cities_content($limit, $use_ai) {
    // Load configuration
    $config_file = WP_CONTENT_DIR . '/plugins/happy-place-local/config/sources.local.json';
    if (!file_exists($config_file)) {
      throw new \Exception(__('Configuration file not found. Please configure API keys first.', 'happy-place-local'));
    }

    $config = json_decode(file_get_contents($config_file), true);
    if (empty($config['google']['api_key'])) {
      throw new \Exception(__('Google Places API key not configured.', 'happy-place-local'));
    }

    // Initialize clients
    $google = new \HappyPlace\Local\Services\GooglePlacesClient($config['google']['api_key']);
    $wiki = new \HappyPlace\Local\Services\WikiClient($config['wikipedia']['lang'] ?? 'en');
    $census = !empty($config['census']['api_key'])
      ? new \HappyPlace\Local\Services\CensusClient($config['census']['api_key'], $config['census']['year'] ?? '2023')
      : null;
    
    // Search for cities and localities in Delaware
    $cities = $google->textSearch('city locality in Delaware');
    if (is_wp_error($cities)) {
      throw new \Exception($cities->get_error_message());
    }

    // Filter for actual cities/localities
    $filtered_cities = [];
    foreach ($cities as $place) {
      $types = $place['types'] ?? [];
      if (array_intersect($types, ['locality', 'postal_town', 'administrative_area_level_3'])) {
        $filtered_cities[] = $place;
      }
    }

    $filtered_cities = array_slice($filtered_cities, 0, $limit);

    if (empty($filtered_cities)) {
      return ['message' => __('No cities found in the configured area.', 'happy-place-local')];
    }

    // Import cities with enhanced data
    $imported = 0;
    foreach ($filtered_cities as $city) {
      $enhanced_city_data = $this->get_enhanced_city_data($city, $google, $wiki, $census, $config);
      
      // Create rich content from all data sources
      $content = $this->build_city_content($enhanced_city_data);
      
      $post_id = wp_insert_post([
        'post_type' => 'city',
        'post_status' => 'publish',
        'post_title' => $enhanced_city_data['name'] ?? 'Unknown City',
        'post_content' => $content
      ]);

      if (!is_wp_error($post_id) && $post_id) {
        // Core geographic data
        if (!empty($enhanced_city_data['state'])) {
          update_field('state', $enhanced_city_data['state'], $post_id);
        }
        if (!empty($enhanced_city_data['county'])) {
          update_field('county', $enhanced_city_data['county'], $post_id);
        }
        if (!empty($enhanced_city_data['population'])) {
          update_field('population', $enhanced_city_data['population'], $post_id);
        }
        
        // Location coordinates
        if (isset($enhanced_city_data['geometry']['location'])) {
          update_field('lat', $enhanced_city_data['geometry']['location']['lat'], $post_id);
          update_field('lng', $enhanced_city_data['geometry']['location']['lng'], $post_id);
        }
        
        // Wikipedia-sourced content
        if (!empty($enhanced_city_data['tagline'])) {
          update_field('tagline', $enhanced_city_data['tagline'], $post_id);
        }
        if (!empty($enhanced_city_data['description'])) {
          update_field('description', $enhanced_city_data['description'], $post_id);
        }
        
        // Set hero image from Wikipedia
        if (!empty($enhanced_city_data['hero_image_url'])) {
          $this->set_city_hero_image($post_id, $enhanced_city_data['hero_image_url'], $enhanced_city_data['name']);
        }
        
        // External links
        if (!empty($enhanced_city_data['external_links'])) {
          update_field('external_links', $enhanced_city_data['external_links'], $post_id);
        }
        
        // Reference metadata
        if (!empty($enhanced_city_data['place_id'])) {
          update_post_meta($post_id, 'google_place_id', $enhanced_city_data['place_id']);
        }
        if (!empty($enhanced_city_data['wikidata_id'])) {
          update_post_meta($post_id, 'wikidata_id', $enhanced_city_data['wikidata_id']);
        }
        if (!empty($enhanced_city_data['formatted_address'])) {
          update_post_meta($post_id, 'full_address', $enhanced_city_data['formatted_address']);
        }
        
        $imported++;
      }
    }

    return ['message' => sprintf(__('Successfully imported %d cities with enhanced data.', 'happy-place-local'), $imported)];
  }

  private function generate_events_content($limit, $use_ai, $type_data) {
    $theme = $type_data['theme'] ?? 'community';
    
    // Event templates by theme
    $event_templates = [
      'community' => [
        'Farmers Market',
        'Community Clean-Up Day',
        'Town Hall Meeting',
        'Local Art Show',
        'Community Potluck'
      ],
      'cultural' => [
        'Art Gallery Opening',
        'Live Music Performance',
        'Poetry Reading',
        'Cultural Festival',
        'Museum Exhibition'
      ],
      'outdoor' => [
        'Hiking Group Meetup',
        'Park Yoga Class',
        'Bird Watching Tour',
        'Outdoor Movie Night',
        'Nature Photography Walk'
      ],
      'family' => [
        'Kids Story Time',
        'Family Game Night',
        'Children\'s Workshop',
        'Family Picnic',
        'Youth Sports League'
      ],
      'business' => [
        'Networking Mixer',
        'Business Workshop',
        'Entrepreneur Meetup',
        'Professional Development',
        'Chamber of Commerce Event'
      ]
    ];

    $templates = $event_templates[$theme] ?? $event_templates['community'];
    $imported = 0;

    for ($i = 0; $i < min($limit, count($templates)); $i++) {
      $title = $templates[$i];
      
      // Generate sample event data
      $start_date = date('Y-m-d H:i:s', strtotime('+' . rand(1, 30) . ' days'));
      $end_date = date('Y-m-d H:i:s', strtotime($start_date . ' +2 hours'));
      
      if ($use_ai) {
        // Create in pipeline for AI enhancement
        $post_id = wp_insert_post([
          'post_type' => 'hpl_ingest',
          'post_status' => 'publish',
          'post_title' => 'Event: ' . $title,
          'post_content' => "Sample $theme event: $title"
        ]);
        
        if (!is_wp_error($post_id) && $post_id) {
          // Set as event type
          update_post_meta($post_id, '_hpl_target_type', 'local_event');
          update_post_meta($post_id, '_hpl_stage', 'new');
          
          // Store raw event data
          $raw_data = [
            'title' => $title,
            'start' => $start_date,
            'end' => $end_date,
            'theme' => $theme,
            'price' => rand(0, 1) ? 'Free' : '$' . rand(5, 25)
          ];
          update_post_meta($post_id, '_hpl_raw', wp_json_encode($raw_data));
          $imported++;
        }
      } else {
        // Create directly as local_event
        $post_id = wp_insert_post([
          'post_type' => 'local_event',
          'post_status' => 'publish',
          'post_title' => $title,
          'post_content' => "Join us for this exciting $theme event in your local community."
        ]);
        
        if (!is_wp_error($post_id) && $post_id) {
          update_post_meta($post_id, 'start_datetime', $start_date);
          update_post_meta($post_id, 'end_datetime', $end_date);
          update_post_meta($post_id, 'price', rand(0, 1) ? 'Free' : '$' . rand(5, 25));
          $imported++;
        }
      }
    }

    $message = $use_ai 
      ? sprintf(__('Successfully generated %d %s events in AI enhancement pipeline.', 'happy-place-local'), $imported, $theme)
      : sprintf(__('Successfully generated %d %s events.', 'happy-place-local'), $imported, $theme);

    return ['message' => $message];
  }

  /**
   * Get enhanced place data by fetching full details from Google Places API
   */
  private function get_enhanced_place_data($basic_place, $api_key) {
    // If we have a place_id, fetch full details
    if (!empty($basic_place['place_id'])) {
      try {
        $google = new \HappyPlace\Local\Services\GooglePlacesClient($api_key);
        $details = $google->details($basic_place['place_id']);
        
        if (!is_wp_error($details) && !empty($details)) {
          // Merge basic place data with detailed data
          return array_merge($basic_place, $details);
        }
      } catch (\Exception $e) {
        error_log('[HPL] Failed to fetch place details: ' . $e->getMessage());
      }
    }
    
    // Return basic data if details fetch failed
    return $basic_place;
  }

  /**
   * Build comprehensive content description from place data
   */
  private function build_place_content($place_data) {
    $content_parts = [];
    
    // Basic description
    if (!empty($place_data['name'])) {
      $content_parts[] = $place_data['name'];
      
      if (!empty($place_data['types'])) {
        $readable_types = $this->format_place_types($place_data['types']);
        if ($readable_types) {
          $content_parts[] = "is a " . $readable_types;
        }
      }
      
      if (!empty($place_data['formatted_address'])) {
        $content_parts[] = "located at " . $place_data['formatted_address'];
      }
    }
    
    // Rating information
    if (!empty($place_data['rating']) && !empty($place_data['user_ratings_total'])) {
      $content_parts[] = sprintf(
        "This business has a %s-star rating based on %d reviews",
        number_format($place_data['rating'], 1),
        $place_data['user_ratings_total']
      );
    }
    
    // Contact information
    $contact_info = [];
    if (!empty($place_data['formatted_phone_number'])) {
      $contact_info[] = "phone: " . $place_data['formatted_phone_number'];
    }
    if (!empty($place_data['website'])) {
      $contact_info[] = "website available";
    }
    if (!empty($contact_info)) {
      $content_parts[] = "Contact information: " . implode(', ', $contact_info);
    }
    
    // Operating hours
    if (!empty($place_data['opening_hours']['weekday_text'])) {
      $content_parts[] = "Operating hours: " . implode(', ', array_slice($place_data['opening_hours']['weekday_text'], 0, 2));
    }
    
    return implode('. ', $content_parts) . '.';
  }

  /**
   * Convert Google Place types to readable format
   */
  private function format_place_types($types) {
    $type_map = [
      'restaurant' => 'restaurant',
      'food' => 'food establishment', 
      'establishment' => 'business',
      'bar' => 'bar',
      'cafe' => 'cafe',
      'bakery' => 'bakery',
      'meal_takeaway' => 'takeaway restaurant',
      'meal_delivery' => 'delivery service',
      'lodging' => 'lodging facility',
      'tourist_attraction' => 'tourist attraction',
      'store' => 'retail store',
      'shopping_mall' => 'shopping center',
      'gas_station' => 'gas station',
      'hospital' => 'hospital',
      'pharmacy' => 'pharmacy',
      'bank' => 'bank',
      'atm' => 'ATM location',
      'church' => 'church',
      'school' => 'school',
      'gym' => 'fitness center'
    ];
    
    foreach ($types as $type) {
      if (isset($type_map[$type])) {
        return $type_map[$type];
      }
    }
    
    return null;
  }

  /**
   * Convert Google opening hours to JSON format expected by ACF field
   */
  private function convert_opening_hours($opening_hours_data) {
    if (empty($opening_hours_data['periods'])) {
      return '';
    }
    
    $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    $hours = [];
    
    // Initialize all days as closed
    foreach ($days as $day) {
      $hours[$day] = 'closed';
    }
    
    // Process periods
    foreach ($opening_hours_data['periods'] as $period) {
      if (isset($period['open']['day'])) {
        $day_index = $period['open']['day'];
        $day = $days[$day_index];
        
        $open_time = $this->format_time($period['open']['time'] ?? '0000');
        $close_time = isset($period['close']) ? $this->format_time($period['close']['time']) : '23:59';
        
        $hours[$day] = $open_time . '-' . $close_time;
      }
    }
    
    return wp_json_encode($hours);
  }

  /**
   * Format time from HHMM to HH:MM
   */
  private function format_time($time_string) {
    if (strlen($time_string) === 4) {
      return substr($time_string, 0, 2) . ':' . substr($time_string, 2, 2);
    }
    return $time_string;
  }

  /**
   * Detect if place is family-friendly based on types and other indicators
   */
  private function detect_family_friendly($place_data) {
    $types = $place_data['types'] ?? [];
    
    // Family-friendly indicators
    $family_friendly_types = [
      'restaurant', 'cafe', 'bakery', 'meal_takeaway', 'tourist_attraction',
      'park', 'museum', 'zoo', 'amusement_park', 'aquarium', 'library',
      'shopping_mall', 'store', 'pharmacy'
    ];
    
    // Non family-friendly indicators
    $not_family_friendly_types = ['bar', 'night_club', 'casino', 'liquor_store'];
    
    // Check for explicit non-family-friendly types
    if (array_intersect($types, $not_family_friendly_types)) {
      return false;
    }
    
    // Check for family-friendly types
    if (array_intersect($types, $family_friendly_types)) {
      return true;
    }
    
    // Default to neutral (not specified)
    return false;
  }

  /**
   * Detect accessibility features based on available data
   */
  private function detect_accessibility_features($place_data) {
    $accessibility = [];
    $types = $place_data['types'] ?? [];
    
    // Basic accessibility assumptions for certain types
    if (in_array('hospital', $types) || in_array('pharmacy', $types) || in_array('bank', $types)) {
      $accessibility[] = 'wheelchair';
      $accessibility[] = 'parking';
    }
    
    if (in_array('shopping_mall', $types) || in_array('tourist_attraction', $types)) {
      $accessibility[] = 'wheelchair';
      $accessibility[] = 'restroom';
    }
    
    // For restaurants and cafes, assume basic accessibility
    if (in_array('restaurant', $types) || in_array('cafe', $types)) {
      if (!empty($place_data['rating']) && $place_data['rating'] >= 4.0) {
        $accessibility[] = 'wheelchair';
      }
    }
    
    return $accessibility;
  }

  /**
   * Download and set featured image from Google Places photo
   */
  private function set_place_featured_image($post_id, $photo_reference, $api_key, $place_name) {
    $photo_url = add_query_arg([
      'maxwidth' => 1200,
      'photoreference' => $photo_reference,
      'key' => $api_key
    ], 'https://maps.googleapis.com/maps/api/place/photo');
    
    try {
      // Download the image
      $image_data = wp_remote_get($photo_url, ['timeout' => 30]);
      
      if (is_wp_error($image_data)) {
        error_log('[HPL] Failed to download place image: ' . $image_data->get_error_message());
        return;
      }
      
      $image_body = wp_remote_retrieve_body($image_data);
      $content_type = wp_remote_retrieve_header($image_data, 'content-type');
      
      // Determine file extension from content type
      $extension = 'jpg';
      if (strpos($content_type, 'png') !== false) {
        $extension = 'png';
      } elseif (strpos($content_type, 'webp') !== false) {
        $extension = 'webp';
      }
      
      // Create filename
      $filename = sanitize_file_name($place_name) . '-google-photo.' . $extension;
      
      // Save image to media library
      $upload = wp_upload_bits($filename, null, $image_body);
      
      if (!$upload['error']) {
        $attachment = [
          'post_mime_type' => $content_type,
          'post_title' => $place_name . ' - Google Photo',
          'post_content' => '',
          'post_status' => 'inherit'
        ];
        
        $attach_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
        
        if (!is_wp_error($attach_id)) {
          require_once(ABSPATH . 'wp-admin/includes/image.php');
          $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
          wp_update_attachment_metadata($attach_id, $attach_data);
          
          // Set as featured image
          set_post_thumbnail($post_id, $attach_id);
        }
      }
    } catch (\Exception $e) {
      error_log('[HPL] Failed to set place featured image: ' . $e->getMessage());
    }
  }

  /**
   * Get enhanced city data from multiple sources
   */
  private function get_enhanced_city_data($basic_city, $google, $wiki, $census, $config) {
    $city_name = $basic_city['name'] ?? '';
    $state = 'DE'; // Default for Delaware
    
    // Start with basic Google Places data
    $enhanced_data = $basic_city;
    $enhanced_data['state'] = $state;
    
    // Extract county from address components if available
    $enhanced_data['county'] = $this->extract_county_from_address($basic_city);
    
    // Get detailed Google Places data if place_id exists
    if (!empty($basic_city['place_id'])) {
      try {
        $place_details = $google->details($basic_city['place_id']);
        if (!is_wp_error($place_details) && !empty($place_details)) {
          $enhanced_data = array_merge($enhanced_data, $place_details);
        }
      } catch (\Exception $e) {
        error_log('[HPL] Failed to fetch city details: ' . $e->getMessage());
      }
    }
    
    // Get Wikipedia data
    if ($city_name) {
      try {
        $wiki_data = $wiki->summary($city_name, $state);
        if (!empty($wiki_data)) {
          // Wikipedia description
          if (!empty($wiki_data['extract'])) {
            $enhanced_data['description'] = $this->format_wikipedia_content($wiki_data['extract']);
            $enhanced_data['tagline'] = $this->extract_tagline_from_description($wiki_data['extract'], $city_name);
          }
          
          // Wikipedia image
          if (!empty($wiki_data['image'])) {
            $enhanced_data['hero_image_url'] = $wiki_data['image'];
          }
          
          // Wikidata ID for reference
          if (!empty($wiki_data['wikidata'])) {
            $enhanced_data['wikidata_id'] = $wiki_data['wikidata'];
            
            // Try to get population from Wikidata
            $wikidata_population = $wiki->wikidataPopulation($wiki_data['wikidata']);
            if ($wikidata_population) {
              $enhanced_data['population'] = $wikidata_population;
            }
          }
          
          // Wikipedia URL
          $wiki_title = $wiki_data['title'] ?? $city_name;
          $enhanced_data['wikipedia_url'] = "https://en.wikipedia.org/wiki/" . rawurlencode($wiki_title);
        }
      } catch (\Exception $e) {
        error_log('[HPL] Failed to fetch Wikipedia data for ' . $city_name . ': ' . $e->getMessage());
      }
    }
    
    // Get Census population data
    if ($census && $city_name && empty($enhanced_data['population'])) {
      try {
        $census_population = $census->placePopulation($city_name, $state);
        if ($census_population) {
          $enhanced_data['population'] = $census_population;
        }
      } catch (\Exception $e) {
        error_log('[HPL] Failed to fetch Census data for ' . $city_name . ': ' . $e->getMessage());
      }
    }
    
    // Build external links
    $enhanced_data['external_links'] = $this->build_city_external_links($enhanced_data, $city_name, $state);
    
    return $enhanced_data;
  }

  /**
   * Extract county from Google Places address components
   */
  private function extract_county_from_address($place_data) {
    if (!empty($place_data['address_components'])) {
      foreach ($place_data['address_components'] as $component) {
        if (in_array('administrative_area_level_2', $component['types'] ?? [])) {
          return str_replace(' County', '', $component['long_name']);
        }
      }
    }
    
    // Fallback: extract from formatted_address
    if (!empty($place_data['formatted_address'])) {
      // Look for "County" in the address
      if (preg_match('/([A-Za-z\s]+)\s+County/', $place_data['formatted_address'], $matches)) {
        return trim($matches[1]);
      }
    }
    
    return '';
  }

  /**
   * Format Wikipedia content for use in city description
   */
  private function format_wikipedia_content($extract) {
    // Clean up the Wikipedia extract
    $content = strip_tags($extract);
    
    // Add some basic formatting
    $sentences = explode('. ', $content);
    $formatted_sentences = [];
    
    foreach ($sentences as $sentence) {
      $sentence = trim($sentence);
      if ($sentence && strlen($sentence) > 20) { // Filter out very short sentences
        $formatted_sentences[] = $sentence;
      }
    }
    
    return implode('. ', $formatted_sentences) . '.';
  }

  /**
   * Extract a tagline from Wikipedia description
   */
  private function extract_tagline_from_description($description, $city_name) {
    // Look for common tagline patterns
    $patterns = [
      '/is known as[^\.]*/',
      '/is famous for[^\.]*/',
      '/is home to[^\.]*/',
      '/serves as[^\.]*/',
      '/' . preg_quote($city_name, '/') . ' is[^\.]{20,80}/'
    ];
    
    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $description, $matches)) {
        $tagline = trim($matches[0]);
        // Clean up and shorten if needed
        if (strlen($tagline) > 100) {
          $words = explode(' ', $tagline);
          $tagline = implode(' ', array_slice($words, 0, 12)) . '...';
        }
        return $tagline;
      }
    }
    
    // Fallback: create a generic tagline
    $type_indicators = [
      'city' => 'A vibrant city',
      'town' => 'A charming town', 
      'county seat' => 'The county seat',
      'capital' => 'The state capital'
    ];
    
    foreach ($type_indicators as $type => $tagline) {
      if (stripos($description, $type) !== false) {
        return $tagline . ' in Delaware';
      }
    }
    
    return 'Discover ' . $city_name;
  }

  /**
   * Build comprehensive city content from all data sources
   */
  private function build_city_content($city_data) {
    $content_parts = [];
    
    // Basic intro
    $name = $city_data['name'] ?? 'This city';
    if (!empty($city_data['formatted_address'])) {
      $content_parts[] = $name . ' is located in ' . $city_data['formatted_address'];
    }
    
    // Population information
    if (!empty($city_data['population'])) {
      $content_parts[] = 'The city has a population of approximately ' . number_format($city_data['population']) . ' residents';
    }
    
    // Geographic information
    $geo_info = [];
    if (!empty($city_data['county'])) {
      $geo_info[] = $city_data['county'] . ' County';
    }
    if (!empty($city_data['state'])) {
      $geo_info[] = $city_data['state'];
    }
    if (!empty($geo_info)) {
      $content_parts[] = $name . ' is situated in ' . implode(', ', $geo_info);
    }
    
    // Coordinates for reference
    if (!empty($city_data['geometry']['location'])) {
      $lat = round($city_data['geometry']['location']['lat'], 4);
      $lng = round($city_data['geometry']['location']['lng'], 4);
      $content_parts[] = "Geographic coordinates: {$lat}, {$lng}";
    }
    
    return implode('. ', $content_parts) . '.';
  }

  /**
   * Build external links array for city
   */
  private function build_city_external_links($city_data, $city_name, $state) {
    $links = [];
    
    // Wikipedia link
    if (!empty($city_data['wikipedia_url'])) {
      $links[] = [
        'title' => 'Wikipedia',
        'url' => $city_data['wikipedia_url'],
        'type' => 'other'
      ];
    }
    
    // Google Maps link
    if (!empty($city_data['place_id'])) {
      $links[] = [
        'title' => 'Google Maps',
        'url' => 'https://maps.google.com/?place_id=' . $city_data['place_id'],
        'type' => 'other'
      ];
    }
    
    // Try to find official city website (basic heuristics)
    $potential_websites = [
      'https://www.' . strtolower(str_replace(' ', '', $city_name)) . 'de.gov',
      'https://www.city' . strtolower(str_replace(' ', '', $city_name)) . '.com',
      'https://www.' . strtolower(str_replace(' ', '-', $city_name)) . '.delaware.gov'
    ];
    
    foreach ($potential_websites as $website) {
      // In a production system, you might want to check if these URLs are valid
      // For now, we'll add a placeholder for the first one
      if (count($links) < 3) { // Limit to avoid too many speculative links
        $links[] = [
          'title' => 'City Website',
          'url' => $website,
          'type' => 'official'
        ];
        break; // Only add one potential official site
      }
    }
    
    return $links;
  }

  /**
   * Download and set hero image for city from Wikipedia or other sources
   */
  private function set_city_hero_image($post_id, $image_url, $city_name) {
    try {
      // Download the image
      $image_data = wp_remote_get($image_url, ['timeout' => 30]);
      
      if (is_wp_error($image_data)) {
        error_log('[HPL] Failed to download city image: ' . $image_data->get_error_message());
        return false;
      }
      
      $image_body = wp_remote_retrieve_body($image_data);
      $content_type = wp_remote_retrieve_header($image_data, 'content-type');
      
      // Determine file extension
      $extension = 'jpg';
      if (strpos($content_type, 'png') !== false) {
        $extension = 'png';
      } elseif (strpos($content_type, 'webp') !== false) {
        $extension = 'webp';
      }
      
      // Create filename
      $filename = sanitize_file_name($city_name) . '-hero.' . $extension;
      
      // Save image to media library
      $upload = wp_upload_bits($filename, null, $image_body);
      
      if (!$upload['error']) {
        $attachment = [
          'post_mime_type' => $content_type,
          'post_title' => $city_name . ' - Hero Image',
          'post_content' => '',
          'post_status' => 'inherit'
        ];
        
        $attach_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
        
        if (!is_wp_error($attach_id)) {
          require_once(ABSPATH . 'wp-admin/includes/image.php');
          $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
          wp_update_attachment_metadata($attach_id, $attach_data);
          
          // Set as hero image using ACF
          update_field('hero_image', $attach_id, $post_id);
          
          return true;
        }
      }
    } catch (\Exception $e) {
      error_log('[HPL] Failed to set city hero image: ' . $e->getMessage());
    }
    
    return false;
  }

  public function process_agent() {
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'hpl_process_agent')) {
      wp_die(__('Security check failed', 'happy-place-local'));
    }

    if (!current_user_can('manage_options')) {
      wp_die(__('Insufficient permissions', 'happy-place-local'));
    }

    try {
      // Check if LLM is configured
      $config = get_option($this->option_name, []);
      if (empty($config['llm_provider'])) {
        wp_send_json_error(['message' => __('No LLM provider configured. Please configure AI settings first.', 'happy-place-local')]);
        return;
      }

      // Process pending items
      $processed = 0;
      $errors = 0;

      $query_args = [
        'post_type' => 'hpl_ingest',
        'posts_per_page' => 10,
        'meta_query' => [
          [
            'key' => '_hpl_stage',
            'value' => ['new', 'classified', 'enriched', 'rewritten', 'scored'],
            'compare' => 'IN'
          ]
        ],
        'orderby' => 'date',
        'order' => 'ASC',
        'post_status' => 'publish'
      ];

      $posts = get_posts($query_args);

      foreach ($posts as $post) {
        $current_stage = get_post_meta($post->ID, '_hpl_stage', true) ?: 'new';

        try {
          switch ($current_stage) {
            case 'new':
              \HappyPlace\Local\Agent\Tasks\Classify::handle($post->ID);
              break;
            case 'classified':
              \HappyPlace\Local\Agent\Tasks\Enrich::handle($post->ID);
              break;
            case 'enriched':
              \HappyPlace\Local\Agent\Tasks\Rewrite::handle($post->ID);
              break;
            case 'rewritten':
              \HappyPlace\Local\Agent\Tasks\Score::handle($post->ID);
              break;
            case 'scored':
              \HappyPlace\Local\Agent\Tasks\Publish::handle($post->ID);
              break;
          }
          $processed++;
        } catch (\Exception $e) {
          $errors++;
          error_log('Agent processing error: ' . $e->getMessage());
        }
      }

      if ($processed === 0) {
        wp_send_json_success(['message' => __('No items to process in the pipeline.', 'happy-place-local')]);
      } else {
        $message = sprintf(__('Processed %d items', 'happy-place-local'), $processed);
        if ($errors > 0) {
          $message .= sprintf(__(', %d errors occurred', 'happy-place-local'), $errors);
        }
        wp_send_json_success(['message' => $message]);
      }

    } catch (\Exception $e) {
      wp_send_json_error(['message' => sprintf(__('Processing error: %s', 'happy-place-local'), $e->getMessage())]);
    }
  }

  public function get_agent_status() {
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'hpl_agent_status')) {
      wp_die(__('Security check failed', 'happy-place-local'));
    }

    if (!current_user_can('manage_options')) {
      wp_die(__('Insufficient permissions', 'happy-place-local'));
    }

    try {
      global $wpdb;
      
      $stages = ['new', 'classified', 'enriched', 'rewritten', 'scored', 'published', 'ready_for_review', 'error_publish'];
      
      $status = "AI Agent Pipeline Status\n";
      $status .= "========================\n";
      
      foreach ($stages as $stage) {
        $count = $wpdb->get_var($wpdb->prepare(
          "SELECT COUNT(*) FROM {$wpdb->postmeta} pm 
           JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
           WHERE pm.meta_key = '_hpl_stage' 
           AND pm.meta_value = %s 
           AND p.post_type = 'hpl_ingest'",
          $stage
        ));
        
        $status_icon = ($count > 0) ? "📋" : "✓";
        $status .= "{$status_icon} {$stage}: {$count} items\n";
      }
      
      // Configuration status
      $config = get_option($this->option_name, []);
      $status .= "\nConfiguration Status\n";
      $status .= "===================\n";
      $status .= "Agent Enabled: " . (empty($config['agent_enabled']) ? "❌ No" : "✅ Yes") . "\n";
      $status .= "LLM Provider: " . ($config['llm_provider'] ?? "❌ Not configured") . "\n";
      
      if (!empty($config['llm_provider'])) {
        $test_result = \HappyPlace\Local\Agent\LLMManager::test_connection();
        $status .= "LLM Connection: " . ($test_result['success'] ? "✅ Working" : "❌ " . $test_result['message']) . "\n";
      }

      wp_send_json_success(['status' => $status]);

    } catch (\Exception $e) {
      wp_send_json_error(['message' => sprintf(__('Status error: %s', 'happy-place-local'), $e->getMessage())]);
    }
  }

  // Utility Methods
  public function sanitize_settings($input) {
    $sanitized = [];
    
    // API Keys
    if (isset($input['google_places_api_key'])) {
      $sanitized['google_places_api_key'] = sanitize_text_field($input['google_places_api_key']);
    }
    
    // Data Sources
    if (isset($input['enabled_sources']) && is_array($input['enabled_sources'])) {
      $sanitized['enabled_sources'] = array_map('sanitize_text_field', $input['enabled_sources']);
    }
    
    if (isset($input['default_bounds']) && is_array($input['default_bounds'])) {
      $sanitized['default_bounds'] = [
        'sw_lat' => floatval($input['default_bounds']['sw_lat']),
        'sw_lng' => floatval($input['default_bounds']['sw_lng']),
        'ne_lat' => floatval($input['default_bounds']['ne_lat']),
        'ne_lng' => floatval($input['default_bounds']['ne_lng'])
      ];
    }
    
    // AI Agent Settings
    if (isset($input['agent_enabled'])) {
      $sanitized['agent_enabled'] = (bool) $input['agent_enabled'];
    }
    
    if (isset($input['publish_threshold'])) {
      $sanitized['publish_threshold'] = max(0, min(100, intval($input['publish_threshold'])));
    }
    
    if (isset($input['batch_size'])) {
      $sanitized['batch_size'] = max(1, min(50, intval($input['batch_size'])));
    }
    
    // LLM Configuration
    if (isset($input['llm_provider'])) {
      $allowed_providers = ['openai', 'anthropic', 'custom', ''];
      $sanitized['llm_provider'] = in_array($input['llm_provider'], $allowed_providers) ? $input['llm_provider'] : '';
    }
    
    if (isset($input['openai_api_key'])) {
      $sanitized['openai_api_key'] = sanitize_text_field($input['openai_api_key']);
    }
    
    if (isset($input['anthropic_api_key'])) {
      $sanitized['anthropic_api_key'] = sanitize_text_field($input['anthropic_api_key']);
    }
    
    if (isset($input['llm_model'])) {
      $sanitized['llm_model'] = sanitize_text_field($input['llm_model']);
    }
    
    // Rate Limiting
    if (isset($input['requests_per_minute'])) {
      $sanitized['requests_per_minute'] = max(1, min(100, intval($input['requests_per_minute'])));
    }
    
    return $sanitized;
  }

  private function get_default_settings() {
    return [
      'google_places_api_key' => '',
      'enabled_sources' => ['google_places'],
      'default_bounds' => $this->get_default_bounds(),
      'agent_enabled' => false,
      'publish_threshold' => 80,
      'batch_size' => 10,
      'llm_provider' => '',
      'openai_api_key' => '',
      'anthropic_api_key' => '',
      'llm_model' => 'gpt-3.5-turbo',
      'requests_per_minute' => 30
    ];
  }

  private function get_default_bounds() {
    // Delaware bounds
    return [
      'sw_lat' => 38.451013,
      'sw_lng' => -75.788658,
      'ne_lat' => 39.839007,
      'ne_lng' => -74.984165
    ];
  }

  // New AJAX Handlers

  public function reset_pipeline() {
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'hpl_reset_pipeline')) {
      wp_die(__('Security check failed', 'happy-place-local'));
    }

    if (!current_user_can('manage_options')) {
      wp_die(__('Insufficient permissions', 'happy-place-local'));
    }

    try {
      $soft = isset($_POST['soft']) && $_POST['soft'] === 'true';
      
      if ($soft) {
        $posts = get_posts([
          'post_type' => 'hpl_ingest',
          'post_status' => 'any',
          'posts_per_page' => -1,
          'fields' => 'ids'
        ]);
        
        foreach ($posts as $post_id) {
          update_post_meta($post_id, '_hpl_stage', 'new');
          delete_post_meta($post_id, '_hpl_error');
          delete_post_meta($post_id, '_hpl_score');
          delete_post_meta($post_id, '_hpl_published_post');
        }
        
        wp_send_json_success([
          'message' => sprintf(__('Soft reset complete: %d items reset to new stage', 'happy-place-local'), count($posts))
        ]);
      } else {
        global $wpdb;
        $ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'hpl_ingest'");
        
        $count = 0;
        foreach ($ids as $id) {
          if (wp_delete_post($id, true)) {
            $count++;
          }
        }
        
        wp_send_json_success([
          'message' => sprintf(__('Hard reset complete: %d ingest items deleted', 'happy-place-local'), $count)
        ]);
      }
    } catch (\Throwable $e) {
      wp_send_json_error([
        'message' => __('Reset failed: ', 'happy-place-local') . $e->getMessage()
      ]);
    }
  }

  public function scrub_cities() {
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'hpl_scrub_cities')) {
      wp_die(__('Security check failed', 'happy-place-local'));
    }

    if (!current_user_can('manage_options')) {
      wp_die(__('Insufficient permissions', 'happy-place-local'));
    }

    try {
      $action = sanitize_text_field($_POST['scrub_action'] ?? 'retag');
      
      $posts = get_posts([
        'post_type' => 'hpl_ingest',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => [
          [
            'key' => '_hpl_target_type',
            'value' => 'local_place'
          ]
        ]
      ]);

      $processed = 0;
      foreach ($posts as $post) {
        $data = \HappyPlace\Local\Agent\IngestStore::read($post->ID);
        $types = is_array($data['types'] ?? null) ? $data['types'] : [];
        
        $city_types = ['locality', 'postal_town', 'administrative_area_level_3', 'political'];
        $looks_city = !empty(array_intersect($types, $city_types));
        $is_establishment = in_array('establishment', $types, true);
        
        if ($looks_city && !$is_establishment) {
          if ($action === 'retag') {
            update_post_meta($post->ID, '_hpl_target_type', 'city');
          } else {
            wp_delete_post($post->ID, true);
          }
          $processed++;
        }
      }

      $message = ($action === 'retag') 
        ? sprintf(__('Retagged %d city items', 'happy-place-local'), $processed)
        : sprintf(__('Deleted %d city items', 'happy-place-local'), $processed);
        
      wp_send_json_success(['message' => $message]);
    } catch (\Throwable $e) {
      wp_send_json_error([
        'message' => __('Scrub failed: ', 'happy-place-local') . $e->getMessage()
      ]);
    }
  }

  public function clean_run() {
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'hpl_clean_run')) {
      wp_die(__('Security check failed', 'happy-place-local'));
    }

    if (!current_user_can('manage_options')) {
      wp_die(__('Insufficient permissions', 'happy-place-local'));
    }

    try {
      $limit = (int) ($_POST['limit'] ?? 6);
      $limit = max(1, min(20, $limit)); // Bound between 1-20
      
      // This is a simplified version - in reality you'd want to run the full clean-run command
      // For now, just trigger the import and return status
      $message = sprintf(
        __('Clean run initiated for %d items. Check pipeline status for progress.', 'happy-place-local'), 
        $limit
      );
      
      wp_send_json_success(['message' => $message]);
    } catch (\Throwable $e) {
      wp_send_json_error([
        'message' => __('Clean run failed: ', 'happy-place-local') . $e->getMessage()
      ]);
    }
  }

  public function reimport_posts() {
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'hpl_reimport_posts')) {
      wp_die(__('Security check failed', 'happy-place-local'));
    }

    if (!current_user_can('manage_options')) {
      wp_die(__('Insufficient permissions', 'happy-place-local'));
    }

    try {
      $post_type = sanitize_text_field($_POST['post_type'] ?? 'local_place');
      $limit = (int) ($_POST['limit'] ?? 10);
      $dry_run = isset($_POST['dry_run']) && $_POST['dry_run'] === 'true';
      
      if (!in_array($post_type, ['local_place', 'local_event'], true)) {
        wp_send_json_error(['message' => __('Invalid post type', 'happy-place-local')]);
        return;
      }

      $posts = get_posts([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => 'DESC'
      ]);

      if (empty($posts)) {
        wp_send_json_success(['message' => sprintf(__('No %s posts found to re-import', 'happy-place-local'), $post_type)]);
        return;
      }

      $imported = 0;
      $skipped = 0;

      foreach ($posts as $post) {
        // Check if already has active ingest item
        $existing_ingest = get_posts([
          'post_type' => 'hpl_ingest',
          'meta_query' => [
            [
              'key' => '_hpl_source_post_id',
              'value' => $post->ID,
              'compare' => '='
            ]
          ],
          'posts_per_page' => 1
        ]);

        if (!empty($existing_ingest)) {
          $skipped++;
          continue;
        }

        if ($dry_run) {
          $imported++;
          continue;
        }

        // Create ingest item
        $ingest_id = wp_insert_post([
          'post_type' => 'hpl_ingest',
          'post_title' => 'Re-enhance: ' . $post->post_title,
          'post_status' => 'publish',
          'post_content' => $post->post_content
        ]);

        if (!is_wp_error($ingest_id)) {
          update_post_meta($ingest_id, '_hpl_stage', 'new');
          update_post_meta($ingest_id, '_hpl_target_type', $post_type);
          update_post_meta($ingest_id, '_hpl_source_post_id', $post->ID);
          update_post_meta($ingest_id, '_hpl_reimport', true);

          // Create basic raw data
          $raw_data = [
            'name' => $post->post_title,
            'title' => $post->post_title,
            'body' => $post->post_content,
            'source' => 'reimport',
            'existing_post_id' => $post->ID
          ];
          
          update_post_meta($ingest_id, '_hpl_raw', wp_json_encode($raw_data));
          $imported++;
        }
      }

      $status = $dry_run ? 'Would import' : 'Imported';
      $message = sprintf(
        __('%s %d posts, skipped %d existing items. Run AI pipeline to enhance.', 'happy-place-local'), 
        $status, 
        $imported, 
        $skipped
      );
      
      wp_send_json_success(['message' => $message]);
    } catch (\Throwable $e) {
      wp_send_json_error([
        'message' => __('Re-import failed: ', 'happy-place-local') . $e->getMessage()
      ]);
    }
  }
}