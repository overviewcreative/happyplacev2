<?php
namespace HappyPlace\Local\Agent\Tasks;

use HappyPlace\Local\Agent\IngestStore;

class Publish {
  public static function handle(int $post_id){
    try {
      if ($post_id <= 0) {
        error_log('[HPL] Publish: invalid post_id: ' . $post_id);
        return;
      }
      
      $data = IngestStore::read($post_id);
      $classification = IngestStore::read_meta($post_id, '_hpl_classify');
      $score_data = IngestStore::read_meta($post_id, '_hpl_score');
      $score = is_numeric($score_data) ? (int) $score_data : (int) ($score_data['score'] ?? 0);
    
    $target_type = get_post_meta($post_id, '_hpl_target_type', true);
    $auto = apply_filters('hpl/agent/auto_publish', $score >= 80, $score, $post_id, $data);

    if (!$auto) { 
      IngestStore::stage($post_id, 'ready_for_review'); 
      return; 
    }

    if ($target_type === 'local_place') {
      // Check if this is a re-import that should update existing post
      $existing_post_id = get_post_meta($post_id, '_hpl_source_post_id', true);
      $is_reimport = get_post_meta($post_id, '_hpl_reimport', true);
      
      if ($is_reimport && $existing_post_id) {
        // Update existing local_place post with enhanced data
        $updated_id = self::update_local_place($existing_post_id, $post_id, $data, $classification);
        if ($updated_id) {
          IngestStore::stage($post_id, 'published');
          IngestStore::write($post_id, '_hpl_published_post', $updated_id);
          error_log('[HPL] Publish: Enhanced existing local_place #' . $updated_id);
        } else {
          update_post_meta($post_id, '_hpl_error', 'Failed to update existing place post');
          IngestStore::stage($post_id, 'error_publish');
        }
      } else {
        // Create new local_place post
        $place_id = self::create_local_place($post_id, $data, $classification);
        if ($place_id) {
          IngestStore::stage($post_id, 'published');
          IngestStore::write($post_id, '_hpl_published_post', $place_id);
        } else {
          update_post_meta($post_id, '_hpl_error', 'Failed to create place post');
          IngestStore::stage($post_id, 'error_publish');
        }
      }
    } else {
        // Default: create local_event
        $event_id = wp_insert_post([
          'post_type'   => 'local_event',
          'post_status' => 'draft',
          'post_title'  => $data['title'] ?? 'Local Event',
          'post_content'=> get_post_meta($post_id, '_hpl_rewrite_md', true)
        ]);
        
        if (!is_wp_error($event_id) && $event_id) {
          if (!empty($data['start'])) update_post_meta($event_id, 'start_datetime', $data['start']);
          if (!empty($data['end']))   update_post_meta($event_id, 'end_datetime', $data['end']);
          if (!empty($data['price'])) update_post_meta($event_id, 'price', $data['price']);
          if (!empty($data['city_slug'])) update_post_meta($event_id, 'hpl_city_slug', sanitize_title($data['city_slug']));
          IngestStore::stage($post_id, 'published');
          IngestStore::write($post_id, '_hpl_published_post', $event_id);
        } else {
          update_post_meta($post_id, '_hpl_error', 'Failed to create event post');
          IngestStore::stage($post_id, 'error_publish');
        }
      }
    } catch (\Throwable $e) {
      error_log('[HPL] Publish: fatal error for #' . $post_id . ': ' . $e->getMessage());
      update_post_meta($post_id, '_hpl_error', 'Publishing failed: ' . $e->getMessage());
      IngestStore::stage($post_id, 'error_publish');
    }
  }

  private static function create_local_place(int $ingest_id, array $raw_data, array $classification): ?int {
    $name = $raw_data['name'] ?? '';
    if (!$name) return null;

    $rewritten_content = get_post_meta($ingest_id, '_hpl_rewrite_md', true) ?: '';

    $place_id = wp_insert_post([
      'post_type' => 'local_place',
      'post_status' => 'publish',
      'post_title' => $name,
      'post_content' => $rewritten_content
    ]);

    if (is_wp_error($place_id) || !$place_id) {
      return null;
    }

    // Map Google Places data to ACF fields
    $meta_fields = [
      'name' => $name,
      'latitude' => $raw_data['geometry']['location']['lat'] ?? null,
      'longitude' => $raw_data['geometry']['location']['lng'] ?? null,
      'category' => $classification['primary_category'] ?? 'Other',
      'google_place_id' => $raw_data['place_id'] ?? '',
      'google_types' => implode(',', $raw_data['types'] ?? []),
      'address' => $raw_data['formatted_address'] ?? '',
      'phone' => $raw_data['formatted_phone_number'] ?? '',
      'website' => $raw_data['website'] ?? '',
      'rating' => $raw_data['rating'] ?? 0,
      'price_level' => $raw_data['price_level'] ?? 0,
      'opening_hours' => json_encode($raw_data['opening_hours'] ?? [])
    ];

    // Add AI-generated attributes
    if (!empty($classification['price_range'])) {
      $meta_fields['price_range'] = $classification['price_range'];
    }
    if (isset($classification['kid_friendly'])) {
      $meta_fields['kid_friendly'] = $classification['kid_friendly'] ? 1 : 0;
    }
    if (isset($classification['outdoor_seating'])) {
      $meta_fields['outdoor_seating'] = $classification['outdoor_seating'] ? 1 : 0;
    }
    if (isset($classification['parking_available'])) {
      $meta_fields['parking_available'] = $classification['parking_available'] ? 1 : 0;
    }
    if (isset($classification['wheelchair_accessible'])) {
      $meta_fields['wheelchair_accessible'] = $classification['wheelchair_accessible'] ? 1 : 0;
    }

    // Save metadata
    foreach ($meta_fields as $key => $value) {
      if ($value !== null && $value !== '') {
        update_post_meta($place_id, $key, $value);
      }
    }

    // Store tags and categories as metadata since taxonomies aren't registered yet
    if (!empty($classification['tags'])) {
      update_post_meta($place_id, 'tags', implode(',', $classification['tags']));
    }

    if (!empty($classification['secondary_categories'])) {
      update_post_meta($place_id, 'secondary_categories', implode(',', $classification['secondary_categories']));
    }

    return $place_id;
  }

  /**
   * Update existing local_place post with enhanced AI data
   */
  private static function update_local_place(int $existing_post_id, int $ingest_id, array $raw_data, array $classification): ?int {
    if (!get_post($existing_post_id)) {
      error_log('[HPL] Publish: Existing post #' . $existing_post_id . ' not found for update');
      return null;
    }

    try {
      // Get AI-enhanced content
      $enhanced_content = get_post_meta($ingest_id, '_hpl_rewrite_md', true);
      
      // Update post content if AI provided better content
      if (!empty($enhanced_content)) {
        $update_data = [
          'ID' => $existing_post_id,
          'post_content' => $enhanced_content,
          'post_modified' => current_time('mysql'),
          'post_modified_gmt' => current_time('mysql', 1)
        ];
        
        wp_update_post($update_data);
      }

      // Update/enhance metadata with AI-enriched data
      $enhanced_meta = [];
      
      // Enhanced location data
      if (!empty($raw_data['geometry']['location']['lat'])) {
        $enhanced_meta['latitude'] = $raw_data['geometry']['location']['lat'];
      }
      if (!empty($raw_data['geometry']['location']['lng'])) {
        $enhanced_meta['longitude'] = $raw_data['geometry']['location']['lng'];
      }
      
      // Enhanced contact information
      if (!empty($raw_data['formatted_address'])) {
        $enhanced_meta['address'] = $raw_data['formatted_address'];
      }
      if (!empty($raw_data['formatted_phone_number'])) {
        $enhanced_meta['phone'] = $raw_data['formatted_phone_number'];
      }
      if (!empty($raw_data['website'])) {
        $enhanced_meta['website'] = $raw_data['website'];
      }
      
      // Enhanced business data
      if (!empty($raw_data['rating'])) {
        $enhanced_meta['rating'] = $raw_data['rating'];
      }
      if (!empty($raw_data['user_ratings_total'])) {
        $enhanced_meta['total_ratings'] = $raw_data['user_ratings_total'];
      }
      if (!empty($raw_data['opening_hours'])) {
        $enhanced_meta['opening_hours'] = json_encode($raw_data['opening_hours']);
      }
      if (!empty($raw_data['price_level'])) {
        $enhanced_meta['price_level'] = $raw_data['price_level'];
      }
      
      // AI-generated attributes
      if (!empty($classification['primary_category'])) {
        $enhanced_meta['category'] = $classification['primary_category'];
      }
      if (!empty($classification['price_range'])) {
        $enhanced_meta['price_range'] = $classification['price_range'];
      }
      if (isset($classification['kid_friendly'])) {
        $enhanced_meta['kid_friendly'] = $classification['kid_friendly'] ? 1 : 0;
      }
      if (isset($classification['outdoor_seating'])) {
        $enhanced_meta['outdoor_seating'] = $classification['outdoor_seating'] ? 1 : 0;
      }
      if (isset($classification['parking_available'])) {
        $enhanced_meta['parking_available'] = $classification['parking_available'] ? 1 : 0;
      }
      if (isset($classification['wheelchair_accessible'])) {
        $enhanced_meta['wheelchair_accessible'] = $classification['wheelchair_accessible'] ? 1 : 0;
      }
      
      // AI-generated tags and categories
      if (!empty($classification['tags'])) {
        $enhanced_meta['ai_tags'] = implode(',', $classification['tags']);
      }
      if (!empty($classification['secondary_categories'])) {
        $enhanced_meta['secondary_categories'] = implode(',', $classification['secondary_categories']);
      }
      
      // Add enhancement timestamp
      $enhanced_meta['ai_enhanced_date'] = current_time('mysql');
      $enhanced_meta['ai_enhanced_score'] = get_post_meta($ingest_id, '_hpl_score', true);
      
      // Update all enhanced metadata
      foreach ($enhanced_meta as $key => $value) {
        if ($value !== null && $value !== '') {
          update_post_meta($existing_post_id, $key, $value);
        }
      }
      
      // Add note about enhancement
      add_post_meta($existing_post_id, '_hpl_enhancement_log', [
        'date' => current_time('mysql'),
        'ingest_id' => $ingest_id,
        'score' => get_post_meta($ingest_id, '_hpl_score', true),
        'enhanced_fields' => array_keys($enhanced_meta)
      ], false);
      
      return $existing_post_id;
      
    } catch (\Throwable $e) {
      error_log('[HPL] Publish: Failed to update existing post #' . $existing_post_id . ': ' . $e->getMessage());
      return null;
    }
  }
}
