<?php
namespace HappyPlace\Local\Agent;

use HappyPlace\Local\Services\GooglePlacesClient;
use HappyPlace\Local\Services\WikiClient;
use HappyPlace\Local\Services\CensusClient;

if (defined('WP_CLI') && WP_CLI) {

class CLIPlaces {

  /**
   * Fetch local places directly without AI pipeline
   *
   * ## OPTIONS
   *
   * [--q=<query>]
   * : Google Places text search query
   * ---
   * default: "restaurants in Delaware"
   * ---
   *
   * [--limit=<number>]
   * : Number of places to fetch
   * ---
   * default: 12
   * ---
   *
   * [--dry-run]
   * : Show what would be imported without making changes
   *
   * ## EXAMPLES
   *
   *     wp hpl:places fetch --q="restaurants in Rehoboth Beach DE" --limit=12
   *     wp hpl:places fetch --q="coffee shops in Wilmington DE" --limit=8 --dry-run
   *     wp hpl:places fetch --q="attractions in Delaware" --limit=20
   */
  public function fetch($args, $assoc) {
    $query = $assoc['q'] ?? 'restaurants in Delaware';
    $limit = max(1, min(50, intval($assoc['limit'] ?? 12)));
    $dry_run = isset($assoc['dry-run']);
    
    \WP_CLI::log("Fetching local places: '{$query}' (limit: {$limit})");
    if ($dry_run) {
      \WP_CLI::log("DRY RUN - No changes will be made");
    }
    \WP_CLI::log("========================================");

    try {
      // Load configuration
      $config = $this->load_config();
      if (!$config) {
        \WP_CLI::error('Configuration file not found. Please configure API keys first.');
      }

      // Initialize Google client only for now
      $google = new GooglePlacesClient($config['google']['api_key'] ?? '');

      // Simple test - just fetch and show basic info
      \WP_CLI::log("Searching Google Places API...");
      $places = $google->textSearch($query);
      
      if (is_wp_error($places)) {
        \WP_CLI::error('Google Places API error: ' . $places->get_error_message());
      }

      if (empty($places)) {
        \WP_CLI::success('No places found matching the search criteria.');
        return;
      }

      // Filter for establishments only
      $filtered_places = [];
      foreach ($places as $place) {
        $types = $place['types'] ?? [];
        $is_establishment = in_array('establishment', $types);
        $is_city = array_intersect($types, ['locality', 'postal_town', 'administrative_area_level_3']);
        
        if ($is_establishment && !$is_city) {
          $filtered_places[] = $place;
        }
      }

      $filtered_places = array_slice($filtered_places, 0, $limit);
      
      if (empty($filtered_places)) {
        \WP_CLI::success('No business establishments found (filtered out cities/localities).');
        return;
      }

      \WP_CLI::log(sprintf("Found %d places to import", count($filtered_places)));

      // Simple import without enhancement - with dry run test
      if ($dry_run) {
        \WP_CLI::log("DRY RUN: Would import the following places:");
        foreach ($filtered_places as $index => $place) {
          $place_name = $place['name'] ?? 'Unknown Place';
          \WP_CLI::log("📍 " . ($index + 1) . ". {$place_name}");
        }
        \WP_CLI::success(sprintf("Would import %d places", count($filtered_places)));
        return;
      }

      // Real import - direct post creation
      $imported = 0;
      \WP_CLI::log("Starting post creation...");
      
      foreach ($filtered_places as $index => $place) {
        try {
          $place_name = $place['name'] ?? 'Unknown Place';
          \WP_CLI::log("Creating post " . ($index + 1) . ": {$place_name}");
          
          // Create the local_place post
          $post_id = wp_insert_post([
            'post_type' => 'local_place',
            'post_status' => 'publish',
            'post_title' => $place_name,
            'post_content' => $place['formatted_address'] ?? ''
          ]);
          
          if (is_wp_error($post_id)) {
            \WP_CLI::warning("Failed to create {$place_name}: " . $post_id->get_error_message());
            continue;
          }
          
          // Add basic metadata
          if (!empty($place['geometry']['location'])) {
            update_post_meta($post_id, 'latitude', $place['geometry']['location']['lat']);
            update_post_meta($post_id, 'longitude', $place['geometry']['location']['lng']);
          }
          
          if (!empty($place['place_id'])) {
            update_post_meta($post_id, 'google_place_id', $place['place_id']);
          }
          
          if (!empty($place['formatted_address'])) {
            update_post_meta($post_id, 'address', $place['formatted_address']);
          }
          
          if (!empty($place['formatted_phone_number'])) {
            update_post_meta($post_id, 'phone', $place['formatted_phone_number']);
          }
          
          if (!empty($place['website'])) {
            update_post_meta($post_id, 'website', $place['website']);
          }
          
          if (!empty($place['rating'])) {
            update_post_meta($post_id, 'rating', $place['rating']);
          }
          
          \WP_CLI::log("✓ Created: {$place_name} (ID: {$post_id})");
          $imported++;
          
        } catch (\Exception $e) {
          \WP_CLI::warning("Error creating {$place_name}: " . $e->getMessage());
        }
      }

      // Summary
      $action = $dry_run ? 'Would import' : 'Imported';
      \WP_CLI::success(sprintf("%s %d places successfully", $action, $imported));

    } catch (\Exception $e) {
      \WP_CLI::error('Fetch failed with Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    } catch (\Error $e) {
      \WP_CLI::error('Fetch failed with Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    }
  }

  /**
   * Complete pipeline: fetch → tag → run pipeline → report
   *
   * ## OPTIONS
   *
   * --q=<query>
   * : Google Places text search query
   * ---
   * default: "restaurants in Delaware"
   * ---
   *
   * [--limit=<number>]
   * : Number of places to fetch
   * ---
   * default: 12
   * ---
   *
   * ## EXAMPLES
   *
   *     wp hpl:places clean-run --q="restaurants in Rehoboth Beach DE" --limit=12
   *     wp hpl:places clean-run --q="cafes in Wilmington DE" --limit=8
   */
  public function clean_run($args, $assoc) {
    $query = $assoc['q'] ?? 'restaurants in Delaware';
    $limit = max(1, min(50, intval($assoc['limit'] ?? 12)));

    \WP_CLI::log("Starting clean run: '{$query}' (limit: {$limit})");
    \WP_CLI::log("=====================================");

    // Step 1: Fetch places
    \WP_CLI::log("Step 1: Fetching places...");
    try {
      \WP_CLI::runcommand("hpl:cities fetch --source=text --q=\"{$query}\" --limit={$limit} --ai --only=places", [
        'launch' => false,
        'exit_error' => false
      ]);
    } catch (\Exception $e) {
      \WP_CLI::warning("Fetch command failed: " . $e->getMessage());
    }

    // Step 2: Run full pipeline
    \WP_CLI::log("\nStep 2: Running AI pipeline...");
    $stages = ['new', 'classified', 'enriched', 'rewritten', 'scored'];
    
    foreach ($stages as $stage) {
      \WP_CLI::log("Processing stage: {$stage}");
      try {
        \WP_CLI::runcommand("hpl:agent run --stage={$stage} --target=local_place --batch-size=20", [
          'launch' => false,
          'exit_error' => false
        ]);
      } catch (\Exception $e) {
        \WP_CLI::warning("Stage {$stage} failed: " . $e->getMessage());
      }
    }

    // Step 3: Generate summary report
    \WP_CLI::log("\nStep 3: Summary Report");
    \WP_CLI::log("=====================");
    
    $this->generate_summary_report();
  }

  /**
   * Generate a summary table of ingest items and their status
   */
  private function generate_summary_report() {
    global $wpdb;

    $results = $wpdb->get_results("
      SELECT 
        p.ID,
        p.post_title,
        MAX(CASE WHEN pm.meta_key = '_hpl_stage' THEN pm.meta_value END) as stage,
        MAX(CASE WHEN pm.meta_key = '_hpl_score' THEN pm.meta_value END) as score,
        MAX(CASE WHEN pm.meta_key = '_hpl_published_post' THEN pm.meta_value END) as published_id,
        MAX(CASE WHEN pm.meta_key = '_hpl_error' THEN pm.meta_value END) as error_msg,
        MAX(CASE WHEN pm.meta_key = '_hpl_target_type' THEN pm.meta_value END) as target_type
      FROM {$wpdb->posts} p
      LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
      WHERE p.post_type = 'hpl_ingest'
        AND p.post_status = 'publish'
      GROUP BY p.ID, p.post_title
      ORDER BY p.ID DESC
      LIMIT 50
    ");

    if (empty($results)) {
      \WP_CLI::log("No ingest items found");
      return;
    }

    $table_data = [];
    foreach ($results as $row) {
      $status = $row->stage ?: 'new';
      if ($row->error_msg) {
        $status .= " (ERROR)";
      }
      
      $published = $row->published_id ? "#{$row->published_id}" : '—';
      $score = $row->score ?: '—';
      $target = $row->target_type ?: 'local_place';
      
      $table_data[] = [
        'ID' => $row->ID,
        'Title' => wp_trim_words($row->post_title, 6),
        'Target' => $target,
        'Stage' => $status,
        'Score' => $score,
        'Published' => $published
      ];
    }

    \WP_CLI\Utils\format_items('table', $table_data, ['ID', 'Title', 'Target', 'Stage', 'Score', 'Published']);
    
    // Summary statistics
    $stages = ['new', 'classified', 'enriched', 'rewritten', 'scored', 'published', 'ready_for_review', 'error_publish'];
    \WP_CLI::log("\nPipeline Status Summary:");
    foreach ($stages as $stage) {
      $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->postmeta} pm 
         JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
         WHERE pm.meta_key = '_hpl_stage' 
         AND pm.meta_value = %s 
         AND p.post_type = 'hpl_ingest'",
        $stage
      ));
      
      $icon = ($count > 0) ? "📋" : "✓";
      \WP_CLI::log("{$icon} {$stage}: {$count} items");
    }
  }

  /**
   * Load configuration from file
   */
  private function load_config() {
    $config_file = WP_CONTENT_DIR . '/plugins/happy-place-local/config/sources.local.json';
    if (!file_exists($config_file)) {
      return null;
    }
    return json_decode(file_get_contents($config_file), true);
  }

  /**
   * Get comprehensive place data from all available sources
   */
  private function get_comprehensive_place_data($basic_place, $google, $wiki, $census, $config) {
    $place_name = $basic_place['name'] ?? '';
    
    // Start with Google Places basic data
    $enhanced_data = $basic_place;
    
    // Get detailed Google Places data if place_id exists
    if (!empty($basic_place['place_id'])) {
      try {
        $place_details = $google->details($basic_place['place_id']);
        if (!is_wp_error($place_details) && !empty($place_details)) {
          // Merge detailed data with basic data
          $enhanced_data = array_merge($enhanced_data, $place_details);
        }
      } catch (\Exception $e) {
        \WP_CLI::debug("Failed to fetch place details for {$place_name}: " . $e->getMessage());
      }
    }
    
    // Try to enhance with Wikipedia data for notable places
    if ($place_name && $this->is_notable_place($enhanced_data)) {
      try {
        $wiki_data = $wiki->summary($place_name);
        if (!empty($wiki_data['extract'])) {
          $enhanced_data['wikipedia_description'] = $this->format_wikipedia_content($wiki_data['extract']);
          $enhanced_data['wikipedia_url'] = "https://en.wikipedia.org/wiki/" . rawurlencode($wiki_data['title'] ?? $place_name);
        }
        if (!empty($wiki_data['image'])) {
          $enhanced_data['wikipedia_image'] = $wiki_data['image'];
        }
      } catch (\Exception $e) {
        \WP_CLI::debug("Failed to fetch Wikipedia data for {$place_name}: " . $e->getMessage());
      }
    }
    
    // Enhance with location-based data (city context)
    $enhanced_data['city_context'] = $this->extract_city_context($enhanced_data);
    
    // Add computed fields
    $enhanced_data['comprehensive_description'] = $this->build_comprehensive_description($enhanced_data);
    $enhanced_data['enhanced_external_links'] = $this->build_comprehensive_external_links($enhanced_data);
    
    return $enhanced_data;
  }

  /**
   * Check if this is a notable place that might have Wikipedia entry
   */
  private function is_notable_place($place_data) {
    $types = $place_data['types'] ?? [];
    $notable_types = [
      'tourist_attraction', 'museum', 'amusement_park', 'aquarium', 'zoo',
      'stadium', 'university', 'hospital', 'church', 'synagogue', 'mosque',
      'city_hall', 'courthouse', 'library', 'park', 'campground'
    ];
    
    // Check if it's a notable type
    if (array_intersect($types, $notable_types)) {
      return true;
    }
    
    // Check if it has high rating and many reviews (might be famous)
    $rating = $place_data['rating'] ?? 0;
    $review_count = $place_data['user_ratings_total'] ?? 0;
    
    if ($rating >= 4.5 && $review_count >= 500) {
      return true;
    }
    
    return false;
  }

  /**
   * Format Wikipedia content for use in place descriptions
   */
  private function format_wikipedia_content($extract) {
    // Clean and format Wikipedia extract
    $content = strip_tags($extract);
    $sentences = explode('. ', $content);
    
    // Keep first 2-3 sentences and filter out very short ones
    $formatted_sentences = [];
    $sentence_count = 0;
    
    foreach ($sentences as $sentence) {
      $sentence = trim($sentence);
      if ($sentence && strlen($sentence) > 30 && $sentence_count < 3) {
        $formatted_sentences[] = $sentence;
        $sentence_count++;
      }
    }
    
    return implode('. ', $formatted_sentences) . '.';
  }

  /**
   * Extract city context from place location
   */
  private function extract_city_context($place_data) {
    $context = [
      'city' => '',
      'state' => 'DE',
      'county' => '',
      'neighborhood' => ''
    ];
    
    // Parse formatted address for city information
    if (!empty($place_data['formatted_address'])) {
      $address_parts = explode(', ', $place_data['formatted_address']);
      foreach ($address_parts as $part) {
        $part = trim($part);
        if (preg_match('/^(.+)\s+DE\s+\d{5}/', $part, $matches)) {
          $context['city'] = trim($matches[1]);
        }
      }
    }
    
    // Extract from address components if available
    if (!empty($place_data['address_components'])) {
      foreach ($place_data['address_components'] as $component) {
        $types = $component['types'] ?? [];
        if (in_array('locality', $types)) {
          $context['city'] = $component['long_name'];
        } elseif (in_array('administrative_area_level_2', $types)) {
          $context['county'] = str_replace(' County', '', $component['long_name']);
        } elseif (in_array('neighborhood', $types) || in_array('sublocality', $types)) {
          $context['neighborhood'] = $component['long_name'];
        }
      }
    }
    
    return $context;
  }

  /**
   * Build comprehensive description combining all data sources
   */
  private function build_comprehensive_description($place_data) {
    $content_parts = [];
    $place_name = $place_data['name'] ?? 'This establishment';
    
    // Basic introduction with location
    if (!empty($place_data['city_context']['city'])) {
      $location_text = $place_data['city_context']['city'];
      if (!empty($place_data['city_context']['county'])) {
        $location_text .= ', ' . $place_data['city_context']['county'] . ' County';
      }
      $content_parts[] = $place_name . ' is located in ' . $location_text;
    }
    
    // Business type and category
    if (!empty($place_data['types'])) {
      $readable_type = $this->get_readable_business_type($place_data['types']);
      if ($readable_type) {
        $content_parts[] = 'This ' . $readable_type . ' serves the local community';
      }
    }
    
    // Wikipedia description if available
    if (!empty($place_data['wikipedia_description'])) {
      $content_parts[] = $place_data['wikipedia_description'];
    }
    
    // Rating and reviews
    if (!empty($place_data['rating']) && !empty($place_data['user_ratings_total'])) {
      $content_parts[] = sprintf(
        'The business maintains a %s-star rating based on %d customer reviews',
        number_format($place_data['rating'], 1),
        $place_data['user_ratings_total']
      );
    }
    
    // Contact and hours summary
    $contact_info = [];
    if (!empty($place_data['formatted_phone_number'])) {
      $contact_info[] = 'phone service available';
    }
    if (!empty($place_data['website'])) {
      $contact_info[] = 'online presence maintained';
    }
    if (!empty($place_data['opening_hours']['weekday_text'])) {
      $contact_info[] = 'regular business hours posted';
    }
    
    if (!empty($contact_info)) {
      $content_parts[] = 'Customer services include: ' . implode(', ', $contact_info);
    }
    
    return implode('. ', $content_parts) . '.';
  }

  /**
   * Convert Google Place types to readable business description
   */
  private function get_readable_business_type($types) {
    $type_descriptions = [
      'restaurant' => 'restaurant and dining establishment',
      'cafe' => 'cafe and coffee house',
      'bar' => 'bar and lounge',
      'bakery' => 'bakery and pastry shop',
      'store' => 'retail store',
      'shopping_mall' => 'shopping center',
      'gas_station' => 'gas station and convenience store',
      'lodging' => 'lodging and accommodation facility',
      'tourist_attraction' => 'tourist attraction and landmark',
      'museum' => 'museum and cultural institution',
      'hospital' => 'hospital and medical facility',
      'bank' => 'bank and financial institution',
      'pharmacy' => 'pharmacy and health services provider',
      'gym' => 'fitness center and wellness facility',
      'beauty_salon' => 'beauty salon and personal care service',
      'car_repair' => 'automotive repair and service center',
      'laundry' => 'laundry and cleaning service'
    ];
    
    foreach ($types as $type) {
      if (isset($type_descriptions[$type])) {
        return $type_descriptions[$type];
      }
    }
    
    // Fallback to generic business
    return 'local business establishment';
  }

  /**
   * Build comprehensive external links
   */
  private function build_comprehensive_external_links($place_data) {
    $links = [];
    
    // Google Maps link
    if (!empty($place_data['place_id'])) {
      $links[] = [
        'title' => 'Google Maps',
        'url' => 'https://maps.google.com/?place_id=' . $place_data['place_id'],
        'type' => 'other'
      ];
    }
    
    // Official website
    if (!empty($place_data['website'])) {
      $links[] = [
        'title' => 'Official Website',
        'url' => $place_data['website'],
        'type' => 'official'
      ];
    }
    
    // Wikipedia link if available
    if (!empty($place_data['wikipedia_url'])) {
      $links[] = [
        'title' => 'Wikipedia',
        'url' => $place_data['wikipedia_url'],
        'type' => 'other'
      ];
    }
    
    return $links;
  }

  /**
   * Import enhanced place as local_place post
   */
  private function import_enhanced_place($enhanced_data) {
    $place_name = $enhanced_data['name'] ?? 'Unknown Place';
    $content = $enhanced_data['comprehensive_description'] ?? '';
    
    // Create the post
    $post_id = wp_insert_post([
      'post_type' => 'local_place',
      'post_status' => 'publish',
      'post_title' => $place_name,
      'post_content' => $content
    ]);
    
    if (is_wp_error($post_id) || !$post_id) {
      return false;
    }
    
    // Populate all ACF fields
    $this->populate_place_acf_fields($post_id, $enhanced_data);
    
    // Set featured image (Google Photos or Wikipedia)
    $this->set_place_images($post_id, $enhanced_data);
    
    return $post_id;
  }

  /**
   * Populate all ACF fields with enhanced data
   */
  private function populate_place_acf_fields($post_id, $data) {
    // Core location data
    if (!empty($data['geometry']['location'])) {
      update_field('lat', $data['geometry']['location']['lat'], $post_id);
      update_field('lng', $data['geometry']['location']['lng'], $post_id);
    }
    
    // Contact information
    if (!empty($data['formatted_address'])) {
      update_field('address', $data['formatted_address'], $post_id);
    }
    if (!empty($data['formatted_phone_number']) || !empty($data['international_phone_number'])) {
      $phone = $data['formatted_phone_number'] ?? $data['international_phone_number'];
      update_field('phone', $phone, $post_id);
    }
    if (!empty($data['website'])) {
      update_field('website', $data['website'], $post_id);
    }
    
    // Business details
    if (!empty($data['price_level'])) {
      $price_map = [1 => '$', 2 => '$$', 3 => '$$$', 4 => '$$$$'];
      if (isset($price_map[$data['price_level']])) {
        update_field('price_range', $price_map[$data['price_level']], $post_id);
      }
    }
    
    // Opening hours
    if (!empty($data['opening_hours']['periods'])) {
      $hours_json = $this->convert_opening_hours($data['opening_hours']);
      update_field('hours_json', $hours_json, $post_id);
    }
    
    // Smart feature detection
    $is_family_friendly = $this->detect_family_friendly($data);
    update_field('is_family_friendly', $is_family_friendly, $post_id);
    
    $accessibility = $this->detect_accessibility_features($data);
    if (!empty($accessibility)) {
      update_field('accessibility', $accessibility, $post_id);
    }
    
    // External links
    if (!empty($data['enhanced_external_links'])) {
      update_field('external_links', $data['enhanced_external_links'], $post_id);
    }
    
    // Source attribution
    update_field('source_url', 'https://maps.google.com/?place_id=' . ($data['place_id'] ?? ''), $post_id);
    update_field('attribution', 'Enhanced data from Google Places, Wikipedia, and other sources', $post_id);
    
    // Reference metadata
    if (!empty($data['place_id'])) {
      update_post_meta($post_id, 'google_place_id', $data['place_id']);
    }
    if (!empty($data['rating'])) {
      update_post_meta($post_id, 'google_rating', $data['rating']);
    }
    if (!empty($data['user_ratings_total'])) {
      update_post_meta($post_id, 'google_reviews_count', $data['user_ratings_total']);
    }
    if (!empty($data['types'])) {
      update_post_meta($post_id, 'google_types', implode(',', $data['types']));
    }
  }

  /**
   * Set place images from Google Photos or Wikipedia
   */
  private function set_place_images($post_id, $data) {
    $place_name = $data['name'] ?? 'Place';
    
    // Try Wikipedia image first (usually higher quality)
    if (!empty($data['wikipedia_image'])) {
      if ($this->download_and_set_featured_image($post_id, $data['wikipedia_image'], $place_name . ' - Wikipedia')) {
        return true;
      }
    }
    
    // Fallback to Google Photos
    if (!empty($data['photos'][0]['photo_reference'])) {
      $config = $this->load_config();
      if (!empty($config['google']['api_key'])) {
        $photo_url = add_query_arg([
          'maxwidth' => 1200,
          'photoreference' => $data['photos'][0]['photo_reference'],
          'key' => $config['google']['api_key']
        ], 'https://maps.googleapis.com/maps/api/place/photo');
        
        return $this->download_and_set_featured_image($post_id, $photo_url, $place_name . ' - Google Photo');
      }
    }
    
    return false;
  }

  /**
   * Download and set featured image
   */
  private function download_and_set_featured_image($post_id, $image_url, $title) {
    try {
      $image_data = wp_remote_get($image_url, ['timeout' => 30]);
      
      if (is_wp_error($image_data)) {
        return false;
      }
      
      $image_body = wp_remote_retrieve_body($image_data);
      $content_type = wp_remote_retrieve_header($image_data, 'content-type');
      
      $extension = 'jpg';
      if (strpos($content_type, 'png') !== false) {
        $extension = 'png';
      } elseif (strpos($content_type, 'webp') !== false) {
        $extension = 'webp';
      }
      
      $filename = sanitize_file_name($title) . '.' . $extension;
      $upload = wp_upload_bits($filename, null, $image_body);
      
      if (!$upload['error']) {
        $attachment = [
          'post_mime_type' => $content_type,
          'post_title' => $title,
          'post_content' => '',
          'post_status' => 'inherit'
        ];
        
        $attach_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
        
        if (!is_wp_error($attach_id)) {
          require_once(ABSPATH . 'wp-admin/includes/image.php');
          $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
          wp_update_attachment_metadata($attach_id, $attach_data);
          
          set_post_thumbnail($post_id, $attach_id);
          return true;
        }
      }
    } catch (\Exception $e) {
      \WP_CLI::debug("Failed to download image: " . $e->getMessage());
    }
    
    return false;
  }

  /**
   * Helper methods for data processing (reused from admin class)
   */
  private function convert_opening_hours($opening_hours_data) {
    if (empty($opening_hours_data['periods'])) {
      return '';
    }
    
    $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    $hours = [];
    
    foreach ($days as $day) {
      $hours[$day] = 'closed';
    }
    
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

  private function format_time($time_string) {
    if (strlen($time_string) === 4) {
      return substr($time_string, 0, 2) . ':' . substr($time_string, 2, 2);
    }
    return $time_string;
  }

  private function detect_family_friendly($place_data) {
    $types = $place_data['types'] ?? [];
    
    $family_friendly_types = [
      'restaurant', 'cafe', 'bakery', 'meal_takeaway', 'tourist_attraction',
      'park', 'museum', 'zoo', 'amusement_park', 'aquarium', 'library',
      'shopping_mall', 'store', 'pharmacy'
    ];
    
    $not_family_friendly_types = ['bar', 'night_club', 'casino', 'liquor_store'];
    
    if (array_intersect($types, $not_family_friendly_types)) {
      return false;
    }
    
    return !empty(array_intersect($types, $family_friendly_types));
  }

  private function detect_accessibility_features($place_data) {
    $accessibility = [];
    $types = $place_data['types'] ?? [];
    
    if (in_array('hospital', $types) || in_array('pharmacy', $types) || in_array('bank', $types)) {
      $accessibility[] = 'wheelchair';
      $accessibility[] = 'parking';
    }
    
    if (in_array('shopping_mall', $types) || in_array('tourist_attraction', $types)) {
      $accessibility[] = 'wheelchair';
      $accessibility[] = 'restroom';
    }
    
    if (in_array('restaurant', $types) || in_array('cafe', $types)) {
      if (!empty($place_data['rating']) && $place_data['rating'] >= 4.0) {
        $accessibility[] = 'wheelchair';
      }
    }
    
    return $accessibility;
  }

  /**
   * Preview what would be imported in dry-run mode
   */
  private function preview_place_import($enhanced_data) {
    $name = $enhanced_data['name'] ?? 'Unknown Place';
    $address = $enhanced_data['formatted_address'] ?? 'No address';
    $types = implode(', ', $enhanced_data['types'] ?? []);
    $rating = isset($enhanced_data['rating']) ? $enhanced_data['rating'] . ' stars' : 'No rating';
    $phone = $enhanced_data['formatted_phone_number'] ?? 'No phone';
    $website = !empty($enhanced_data['website']) ? 'Yes' : 'No';
    $wikipedia = !empty($enhanced_data['wikipedia_description']) ? 'Yes' : 'No';
    
    \WP_CLI::log("📍 {$name}");
    \WP_CLI::log("   Address: {$address}");
    \WP_CLI::log("   Types: {$types}");
    \WP_CLI::log("   Rating: {$rating}");
    \WP_CLI::log("   Phone: {$phone}");
    \WP_CLI::log("   Website: {$website}");
    \WP_CLI::log("   Wikipedia: {$wikipedia}");
    \WP_CLI::log("");
  }
}

}