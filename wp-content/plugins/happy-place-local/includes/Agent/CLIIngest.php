<?php
namespace HappyPlace\Local\Agent;

class CLIIngest {

  /**
   * Reset the AI Agent pipeline
   *
   * ## OPTIONS
   *
   * [--soft]
   * : Soft reset - set all items to stage=new and clear errors/scores
   * ---
   * default: false (hard delete)
   * ---
   *
   * ## EXAMPLES
   *
   *     wp hpl:agent reset
   *     wp hpl:agent reset --soft
   */
  public function reset($args, $assoc) {
    global $wpdb;
    
    $soft = isset($assoc['soft']);
    
    if ($soft) {
      // Soft reset - reset stages and clear error data
      $posts = get_posts([
        'post_type' => 'hpl_ingest',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids'
      ]);
      
      $count = 0;
      foreach ($posts as $post_id) {
        update_post_meta($post_id, '_hpl_stage', 'new');
        delete_post_meta($post_id, '_hpl_error');
        delete_post_meta($post_id, '_hpl_score');
        delete_post_meta($post_id, '_hpl_published_post');
        $count++;
      }
      
      \WP_CLI::success("Soft reset {$count} ingest items to 'new' stage");
    } else {
      // Hard reset - delete all hpl_ingest posts
      $ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'hpl_ingest'");
      
      if (empty($ids)) {
        \WP_CLI::success('No ingest items to delete');
        return;
      }
      
      $count = 0;
      foreach ($ids as $id) {
        if (wp_delete_post($id, true)) {
          $count++;
        }
      }
      
      \WP_CLI::success("Deleted {$count} ingest items");
    }
  }

  /**
   * Scrub ingest items that look like cities/localities
   *
   * ## OPTIONS
   *
   * --action=<retag|delete>
   * : Action to take on city-like items
   *
   * ## EXAMPLES
   *
   *     wp hpl:ingest scrub --action=retag
   *     wp hpl:ingest scrub --action=delete
   */
  public function scrub($args, $assoc) {
    $action = $assoc['action'] ?? '';
    
    if (!in_array($action, ['retag', 'delete'])) {
      \WP_CLI::error("Action must be 'retag' or 'delete'");
    }

    $posts = get_posts([
      'post_type' => 'hpl_ingest',
      'post_status' => 'publish',
      'posts_per_page' => -1
    ]);

    $processed = 0;
    $found_cities = 0;

    foreach ($posts as $post) {
      $data = IngestStore::read($post->ID);
      $types = $data['types'] ?? [];
      
      // Check if this looks like a city/locality and not an establishment
      $looks_city = array_intersect($types, ['locality', 'postal_town', 'administrative_area_level_3', 'political']);
      $is_establishment = in_array('establishment', $types);
      
      if (!empty($looks_city) && !$is_establishment) {
        $found_cities++;
        
        if ($action === 'retag') {
          update_post_meta($post->ID, '_hpl_target_type', 'city');
          \WP_CLI::log("  → Retagged #{$post->ID}: {$post->post_title} as city");
        } else {
          wp_delete_post($post->ID, true);
          \WP_CLI::log("  ✗ Deleted #{$post->ID}: {$post->post_title} (city)");
        }
      }
      $processed++;
    }

    \WP_CLI::success("Processed {$processed} items, {$action}ed {$found_cities} city-like items");
  }

  /**
   * Remove API keys from database options
   *
   * ## EXAMPLES
   *
   *     wp hpl:ingest scrub-secrets
   */
  public function scrub_secrets($args, $assoc) {
    $config = get_option('hpl_config', []);
    $removed = [];
    
    // Keys to scrub from database
    $secret_keys = [
      'google_places_api_key',
      'openai_api_key', 
      'anthropic_api_key'
    ];
    
    foreach ($secret_keys as $key) {
      if (isset($config[$key])) {
        unset($config[$key]);
        $removed[] = $key;
      }
    }
    
    if (empty($removed)) {
      \WP_CLI::log("No API keys found in database options");
      return;
    }
    
    // Update the option without the secret keys
    update_option('hpl_config', $config);
    
    \WP_CLI::success("Removed " . count($removed) . " API keys from database: " . implode(', ', $removed));
    \WP_CLI::log("Note: API keys can now only be set via constants (HPL_GOOGLE_PLACES_KEY, HPL_OPENAI_API_KEY, etc.)");
  }

  /**
   * Re-import existing published posts back into the AI pipeline for enhancement
   *
   * ## OPTIONS
   *
   * [--post-type=<local_place|local_event>]
   * : Post type to re-import
   * ---
   * default: local_place
   * ---
   *
   * [--limit=<number>]
   * : Number of posts to process
   * ---
   * default: 50
   * ---
   *
   * [--ids=<ids>]
   * : Comma-separated list of specific post IDs to re-import
   *
   * [--dry-run]
   * : Show what would be done without making changes
   *
   * ## EXAMPLES
   *
   *     wp hpl:ingest reimport
   *     wp hpl:ingest reimport --post-type=local_place --limit=10
   *     wp hpl:ingest reimport --ids=123,456,789 --dry-run
   */
  public function reimport($args, $assoc) {
    $post_type = $assoc['post-type'] ?? 'local_place';
    $limit = (int) ($assoc['limit'] ?? 50);
    $dry_run = isset($assoc['dry-run']);
    $specific_ids = isset($assoc['ids']) ? array_map('intval', explode(',', $assoc['ids'])) : [];

    if (!in_array($post_type, ['local_place', 'local_event'], true)) {
      \WP_CLI::error('Invalid post type. Must be local_place or local_event');
    }

    try {
      // Get existing published posts
      $query_args = [
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => $specific_ids ? -1 : $limit,
        'orderby' => 'date',
        'order' => 'DESC'
      ];

      if ($specific_ids) {
        $query_args['post__in'] = $specific_ids;
      }

      $posts = get_posts($query_args);

      if (empty($posts)) {
        \WP_CLI::success("No {$post_type} posts found to re-import.");
        return;
      }

      $imported = 0;
      $skipped = 0;

      foreach ($posts as $post) {
        // Check if this post already has an active ingest item
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
          \WP_CLI::log("Skipping {$post->post_title} - already has active ingest item #{$existing_ingest[0]->ID}");
          $skipped++;
          continue;
        }

        if ($dry_run) {
          \WP_CLI::log("Would re-import: #{$post->ID} {$post->post_title}");
          $imported++;
          continue;
        }

        // Create new hpl_ingest post
        $ingest_data = [
          'post_type' => 'hpl_ingest',
          'post_title' => 'Re-enhance: ' . $post->post_title,
          'post_status' => 'publish',
          'post_content' => $post->post_content
        ];

        $ingest_id = wp_insert_post($ingest_data);

        if (is_wp_error($ingest_id)) {
          \WP_CLI::warning("Failed to create ingest for #{$post->ID}: " . $ingest_id->get_error_message());
          continue;
        }

        // Set pipeline metadata
        update_post_meta($ingest_id, '_hpl_stage', 'new');
        update_post_meta($ingest_id, '_hpl_target_type', $post_type);
        update_post_meta($ingest_id, '_hpl_source_post_id', $post->ID);
        update_post_meta($ingest_id, '_hpl_reimport', true);

        // Extract and create raw data from existing post metadata
        $raw_data = $this->extract_raw_data_from_post($post, $post_type);
        update_post_meta($ingest_id, '_hpl_raw', wp_json_encode($raw_data));

        \WP_CLI::log("✓ Created ingest #{$ingest_id} for {$post->post_title}");
        $imported++;
      }

      $status = $dry_run ? 'Would import' : 'Imported';
      \WP_CLI::success("{$status} {$imported} posts, skipped {$skipped} existing items.");

      if (!$dry_run && $imported > 0) {
        \WP_CLI::log("\nNext steps:");
        \WP_CLI::log("1. Run: wp hpl:agent run --stage=new --target={$post_type}");
        \WP_CLI::log("2. Continue through pipeline stages as needed");
        \WP_CLI::log("3. Enhanced data will merge with existing posts");
      }
    } catch (\Throwable $e) {
      \WP_CLI::error('Re-import failed: ' . $e->getMessage());
    }
  }

  /**
   * Extract raw data from existing post for pipeline processing
   */
  private function extract_raw_data_from_post(\WP_Post $post, string $post_type): array {
    $raw_data = [
      'name' => $post->post_title,
      'title' => $post->post_title,
      'body' => $post->post_content,
      'source' => 'reimport',
      'existing_post_id' => $post->ID
    ];

    if ($post_type === 'local_place') {
      // Extract local_place specific data
      $place_meta = [
        'place_id' => get_post_meta($post->ID, 'google_place_id', true),
        'formatted_address' => get_post_meta($post->ID, 'address', true),
        'formatted_phone_number' => get_post_meta($post->ID, 'phone', true),
        'website' => get_post_meta($post->ID, 'website', true),
        'rating' => get_post_meta($post->ID, 'rating', true),
        'latitude' => get_post_meta($post->ID, 'latitude', true),
        'longitude' => get_post_meta($post->ID, 'longitude', true),
        'category' => get_post_meta($post->ID, 'category', true),
        'types' => []
      ];

      // Parse Google types if available
      $google_types = get_post_meta($post->ID, 'google_types', true);
      if ($google_types) {
        $place_meta['types'] = explode(',', $google_types);
      }

      // Add geometry data for Google Places compatibility
      if ($place_meta['latitude'] && $place_meta['longitude']) {
        $place_meta['geometry'] = [
          'location' => [
            'lat' => (float) $place_meta['latitude'],
            'lng' => (float) $place_meta['longitude']
          ]
        ];
      }

      $raw_data = array_merge($raw_data, array_filter($place_meta));

    } elseif ($post_type === 'local_event') {
      // Extract event specific data
      $event_meta = [
        'start' => get_post_meta($post->ID, 'start_datetime', true),
        'end' => get_post_meta($post->ID, 'end_datetime', true),
        'price' => get_post_meta($post->ID, 'price', true),
        'city_slug' => get_post_meta($post->ID, 'hpl_city_slug', true)
      ];

      $raw_data = array_merge($raw_data, array_filter($event_meta));
    }

    return $raw_data;
  }
}