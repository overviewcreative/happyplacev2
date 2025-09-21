<?php
namespace HappyPlace\Local\Agent;

class CLI {

  /**
   * Manually trigger the AI Agent pipeline processing
   *
   * ## OPTIONS
   *
   * [--batch-size=<number>]
   * : Number of items to process per run
   * ---
   * default: 10
   * ---
   *
   * [--stage=<stage>]
   * : Process only items at specific stage (new, classified, enriched, rewritten, scored)
   * ---
   * default: all
   * ---
   *
   * [--target=<local_place|local_event>]
   * : Only process ingest items destined for a given target CPT
   *
   * [--force]
   * : Reprocess items regardless of current stage
   *
   * ## EXAMPLES
   *
   *   wp hpl:agent run
   *   wp hpl:agent run --batch-size=5
   *   wp hpl:agent run --stage=new
   *   wp hpl:agent run --stage=enriched --target=local_place
   *   wp hpl:agent run --force
   */
  public function run($args, $assoc) {
    $batch_size = (int) ($assoc['batch-size'] ?? 10);
    $stage      = $assoc['stage'] ?? 'all';
    $force      = isset($assoc['force']);
    $target     = $assoc['target'] ?? null; // 'local_place' | 'local_event' | null

    if ($stage !== 'all' && !in_array($stage, ['new', 'classified', 'enriched', 'rewritten', 'scored'], true)) {
      \WP_CLI::error("Invalid stage. Must be one of: new, classified, enriched, rewritten, scored");
    }
    if ($target && !in_array($target, ['local_place','local_event'], true)) {
      \WP_CLI::error("Invalid target. Must be local_place or local_event");
    }

    // Config check
    $config = get_option('hpl_config', []);
    if (empty($config['agent_enabled'])) {
      \WP_CLI::warning('AI Agent is not enabled in configuration. Processing anyway...');
    }

    // Only require LLM if this run will actually hit LLM stages
    $llm_stages = ['new','classified','rewritten']; // classify+rewrite need LLM; enrich/score/publish do not
    $will_touch_llm = ($stage === 'all') ? true : in_array($stage, $llm_stages, true);
    if ($will_touch_llm && empty($config['llm_provider'])) {
      \WP_CLI::error('No LLM provider configured. Please configure in Happy Place → Local Places admin.');
    } elseif (!$will_touch_llm && empty($config['llm_provider'])) {
      \WP_CLI::warning('LLM provider not configured; continuing (no LLM needed for this stage).');
    }

    // Build query
    $meta_query = [];
    if ($stage !== 'all') {
      $meta_query[] = [
        'key'     => '_hpl_stage',
        'value'   => $stage,
        'compare' => '='
      ];
    } else {
      $meta_query[] = [
        'key'     => '_hpl_stage',
        'value'   => ['new', 'classified', 'enriched', 'rewritten', 'scored'],
        'compare' => 'IN'
      ];
    }
    if ($target) {
      $meta_query[] = [
        'key'   => '_hpl_target_type',
        'value' => $target
      ];
    }

    $posts = get_posts([
      'post_type'      => 'hpl_ingest',
      'posts_per_page' => $batch_size,
      'meta_query'     => $meta_query,
      'orderby'        => 'date',
      'order'          => 'ASC',
      'post_status'    => 'publish'
    ]);

    if (empty($posts)) {
      \WP_CLI::success('No items to process.');
      return;
    }

    \WP_CLI::log("Found " . count($posts) . " items to process..."
      . ($target ? " (target: {$target})" : ''));

    $processed = 0;
    $errors    = 0;

    foreach ($posts as $post) {
      $current_stage = get_post_meta($post->ID, '_hpl_stage', true) ?: 'new';

      if (!$force && $stage !== 'all' && $current_stage !== $stage) {
        continue;
      }

      \WP_CLI::log("Processing #{$post->ID}: {$post->post_title} (stage: {$current_stage})");

      try {
        // Clear any previous errors before processing
        delete_post_meta($post->ID, '_hpl_error');
        
        switch ($current_stage) {
          case 'new':
            Tasks\Classify::handle($post->ID);
            $classification = IngestStore::read_meta($post->ID, '_hpl_classify') ?: [];
            $confidence = is_array($classification) ? ($classification['confidence'] ?? 0) : 0;
            \WP_CLI::log("  ✓ Classified (confidence: {$confidence})");
            break;

          case 'classified':
            Tasks\Enrich::handle($post->ID);
            \WP_CLI::log("  ✓ Enriched");
            break;

          case 'enriched':
            Tasks\Rewrite::handle($post->ID);
            $rewritten = get_post_meta($post->ID, '_hpl_rewrite_md', true);
            $word_count = $rewritten ? str_word_count(wp_strip_all_tags($rewritten)) : 0;
            \WP_CLI::log("  ✓ Rewritten ({$word_count} words)");
            break;

          case 'rewritten':
            Tasks\Score::handle($post->ID);
            // Score is stored as scalar meta; just cast
            $score = (int) get_post_meta($post->ID, '_hpl_score', true);
            \WP_CLI::log("  ✓ Scored ({$score}/100)");
            break;

          case 'scored':
            Tasks\Publish::handle($post->ID);
            $published_id = get_post_meta($post->ID, '_hpl_published_post', true);
            $new_stage    = get_post_meta($post->ID, '_hpl_stage', true);
            if ($published_id) {
              $post_type = get_post_type($published_id) ?: 'unknown';
              \WP_CLI::log("  ✓ Published ({$post_type} #{$published_id})");
            } elseif ($new_stage === 'ready_for_review') {
              \WP_CLI::log("  → Ready for review (score below threshold)");
            } else {
              $err = get_post_meta($post->ID, '_hpl_error', true);
              \WP_CLI::log("  ✗ Publish failed".($err ? " — {$err}" : ''));
            }
            break;
        }
        $processed++;
      } catch (\Throwable $e) {
        update_post_meta($post->ID, '_hpl_error', 'CLI processing error: ' . $e->getMessage());
        \WP_CLI::warning("  ✗ Error processing #{$post->ID}: " . $e->getMessage());
        $errors++;
      }

      // brief pause to be gentle on APIs
      usleep(100000); // 0.1 sec
    }

    \WP_CLI::success("Processed {$processed} item(s)" . ($errors ? " with {$errors} errors" : ""));
  }

  /**
   * Show status of the AI Agent pipeline
   *
   * ## OPTIONS
   * [--target=<local_place|local_event>]
   * : Filter counts to a single target type
   *
   * ## EXAMPLES
   *   wp hpl:agent status
   *   wp hpl:agent status --target=local_place
   */
  public function status($args, $assoc) {
    global $wpdb;
    $target = $assoc['target'] ?? null;

    $stages = ['new', 'classified', 'enriched', 'rewritten', 'scored', 'published', 'ready_for_review', 'error_publish'];

    \WP_CLI::log("AI Agent Pipeline Status");
    \WP_CLI::log("========================");

    foreach ($stages as $stage) {
      if ($target) {
        $count = $wpdb->get_var($wpdb->prepare(
          "SELECT COUNT(*) FROM {$wpdb->postmeta} s
           JOIN {$wpdb->posts} p ON s.post_id = p.ID
           JOIN {$wpdb->postmeta} t ON t.post_id = p.ID
           WHERE s.meta_key = '_hpl_stage' AND s.meta_value = %s
           AND t.meta_key = '_hpl_target_type' AND t.meta_value = %s
           AND p.post_type = 'hpl_ingest'",
          $stage, $target
        ));
      } else {
        $count = $wpdb->get_var($wpdb->prepare(
          "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
           JOIN {$wpdb->posts} p ON pm.post_id = p.ID
           WHERE pm.meta_key = '_hpl_stage' AND pm.meta_value = %s
           AND p.post_type = 'hpl_ingest'",
          $stage
        ));
      }

      $status_icon = ($count > 0) ? "📋" : "✓";
      \WP_CLI::log("{$status_icon} {$stage}: {$count} items" . ($target ? " (target: {$target})" : ''));
    }

    // Config echo
    $config = get_option('hpl_config', []);
    \WP_CLI::log("\nConfiguration Status");
    \WP_CLI::log("===================");
    \WP_CLI::log("Agent Enabled: " . (empty($config['agent_enabled']) ? "❌ No" : "✅ Yes"));
    \WP_CLI::log("LLM Provider: " . ($config['llm_provider'] ?? "❌ Not configured"));

    // API Keys status (check constants first, then options)
    $google_key = defined('HPL_GOOGLE_PLACES_KEY') && !empty(HPL_GOOGLE_PLACES_KEY) 
      ? 'constant' 
      : (!empty($config['google_places_api_key']) ? 'option' : 'missing');
    
    $openai_key = defined('HPL_OPENAI_API_KEY') && !empty(HPL_OPENAI_API_KEY)
      ? 'constant'
      : (!empty($config['openai_api_key']) ? 'option' : 'missing');
    
    \WP_CLI::log("Google Places API: " . ($google_key === 'missing' ? "❌ Missing" : "✅ Available ({$google_key})"));
    \WP_CLI::log("OpenAI API: " . ($openai_key === 'missing' ? "❌ Missing" : "✅ Available ({$openai_key})"));

    if (!empty($config['llm_provider'])) {
      try {
        $test_result = \HappyPlace\Local\Agent\LLMManager::test_connection();
        \WP_CLI::log("LLM Connection: " . ($test_result['success'] ? "✅ Working" : "❌ " . $test_result['message']));
      } catch (\Throwable $e) {
        \WP_CLI::log("LLM Connection: ❌ Error testing connection: " . $e->getMessage());
      }
    }
  }
}
