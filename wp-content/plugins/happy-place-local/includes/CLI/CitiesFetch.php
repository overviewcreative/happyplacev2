<?php
namespace HappyPlace\Local\CLI;

use HappyPlace\Local\Services\GooglePlacesClient;
use HappyPlace\Local\Services\WikiClient;
use HappyPlace\Local\Services\CensusClient;
use HappyPlace\Local\Services\CityWriter;

if (defined('WP_CLI') && WP_CLI) {

class CitiesFetch {
  public function fetch($args, $assoc) {
    $conf = $this->load_config();
    $limit = (int)($assoc['limit'] ?? 100);
    $dry   = isset($assoc['dry-run']);
    $use_ai = isset($assoc['ai']);
    $state = $assoc['state'] ?? ($conf['defaults']['state'] ?? 'DE');
    $only = $assoc['only'] ?? 'auto'; // places|cities|auto
    $type = $assoc['type'] ?? null; // Optional Google Places type filter
    $offset = (int)($assoc['offset'] ?? 0); // New: pagination offset
    $strategy = $assoc['strategy'] ?? 'bounds'; // New: search strategy

    $google = new GooglePlacesClient($conf['google']['api_key'] ?? '');
    $wiki   = new WikiClient($conf['wikipedia']['lang'] ?? 'en');
    $census = !empty($conf['census']['api_key'])
      ? new CensusClient($conf['census']['api_key'], $conf['census']['year'] ?? '2023', $conf['census']['dataset'] ?? 'ACS/acs5')
      : null;
    $writer = new CityWriter();

    // Get already imported cities for duplicate checking
    $existing_cities = $this->get_existing_cities();
    \WP_CLI::log(sprintf("Found %d existing cities, will skip duplicates", count($existing_cities)));
    
    // Enhanced search strategies
    $results = $this->fetch_with_strategy($google, $conf, $assoc, $strategy, $offset, $limit, $existing_cities);
    
    if (!$results) { \WP_CLI::warning('No Google results.'); return; }

    $count = 0;
    $skipped = 0;
    $duplicates = 0;
    
    // Apply offset to results
    $results = array_slice($results, $offset);
    
    foreach ($results as $r) {
      if ($count >= $limit) break;
      $name = $r['name'] ?? ''; if (!$name) continue;
      $loc  = $r['geometry']['location'] ?? [];
      $lat  = $loc['lat'] ?? null; $lng = $loc['lng'] ?? null;
      $types = $r['types'] ?? [];

      // Check for duplicates first
      if ($this->is_city_duplicate($r, $existing_cities)) {
        \WP_CLI::log("Skipping duplicate: {$name}");
        $duplicates++;
        continue;
      }

      // Determine if this is a city or a local place
      $is_city = $this->is_city($types);
      $is_establishment = in_array('establishment', $types);
      
      // Apply --only filter
      if ($only === 'places' && $is_city && !$is_establishment) {
        \WP_CLI::log("Skipping city-like result: {$name} [" . implode(', ', $types) . "]");
        $skipped++;
        continue;
      } elseif ($only === 'cities' && !$is_city) {
        \WP_CLI::log("Skipping place-like result: {$name} [" . implode(', ', $types) . "]");
        $skipped++;
        continue;
      }
      
      $type_label = $is_city ? 'city' : 'local_place';

      if ($is_city) {
        // Import as city with Wikipedia data
        $desc = ''; $hero = ''; $population = null;
        $sum = $wiki->summary($name, $state);
        if (!empty($sum['extract'])) $desc = $sum['extract'];
        if (!empty($sum['image']))   $hero = $sum['image'];
        if (!empty($sum['wikidata'])) {
          $wdpop = $wiki->wikidataPopulation($sum['wikidata']);
          if ($wdpop) $population = $wdpop;
        }
        if (!$population && $census) {
          $population = $census->placePopulation($name, $state);
        }

        $payload = [
          'name'        => $name,
          'state'       => $state,
          'county'      => '',
          'lat'         => $lat,
          'lng'         => $lng,
          'population'  => $population,
          'tagline'     => '',
          'description' => $desc,
          'hero_image_url' => $hero,
          'external_links' => [
            ['label'=>'Wikipedia','url'=>"https://en.wikipedia.org/wiki/".rawurlencode($sum['title'] ?? $name)]
          ]
        ];

        if ($dry) {
          \WP_CLI::log("Would upsert city: " . $payload['name'] . " (" . $payload['state'] . ") @ {$lat},{$lng}");
        } else {
          $id = $writer->upsert($payload);
          if ($id) \WP_CLI::log("Upserted city #{$id}: {$name}");
        }
      } else {
        // Import as local_place
        if ($dry) {
          \WP_CLI::log("Would upsert local_place: " . $name . " [" . implode(', ', $types) . "] @ {$lat},{$lng}");
        } else {
          if ($use_ai) {
            $id = $this->import_local_place_to_agent($r);
            if ($id) {
              // Ensure target type is set correctly for places
              update_post_meta($id, '_hpl_target_type', 'local_place');
              \WP_CLI::log("Queued local_place #{$id} for AI processing: {$name}");
            }
          } else {
            $id = $this->import_local_place($r);
            if ($id) \WP_CLI::log("Upserted local_place #{$id}: {$name}");
          }
        }
      }
      $count++;
      sleep(1);
    }
    
    $stats = ["Processed {$count} items"];
    if ($duplicates > 0) $stats[] = "skipped {$duplicates} duplicates";
    if ($skipped > 0) $stats[] = "skipped {$skipped} items due to --only={$only} filter";
    
    \WP_CLI::success(implode(', ', $stats));
    
    // Suggest next batch commands if more results might be available
    if ($count > 0) {
      $next_offset = $offset + $limit;
      \WP_CLI::log("\n📋 To fetch next batch, try:");
      \WP_CLI::log("wp hpl:cities fetch --strategy={$strategy} --offset={$next_offset} --limit={$limit}");
      
      if ($strategy !== 'text') {
        \WP_CLI::log("wp hpl:cities fetch --strategy=text --offset=0 --limit={$limit}  # Try different search terms");
      }
      if ($strategy !== 'county') {
        \WP_CLI::log("wp hpl:cities fetch --strategy=county --offset=0 --limit={$limit}  # Search by counties");
      }
      if ($strategy !== 'radius' && count($existing_cities) > 0) {
        \WP_CLI::log("wp hpl:cities fetch --strategy=radius --offset=0 --limit={$limit}  # Expand from existing");
      }
    }
  }

  private function load_config(): array {
    $file = WP_CONTENT_DIR . '/plugins/happy-place-local/config/sources.local.json';
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?: [];
  }

  private function is_city(array $types): bool {
    $city_types = ['locality', 'political', 'administrative_area_level_3'];
    $place_types = ['establishment', 'food', 'restaurant', 'store', 'tourist_attraction', 'point_of_interest'];
    
    // If it has both city and place types, prioritize based on primary type
    $has_city_type = !empty(array_intersect($types, $city_types));
    $has_place_type = !empty(array_intersect($types, $place_types));
    
    // If it has establishment or business-specific types, it's a place
    if ($has_place_type) return false;
    
    // If it only has political/locality types, it's a city
    if ($has_city_type) return true;
    
    // Default to place for ambiguous cases
    return false;
  }

  private function import_local_place(array $place_data): ?int {
    $name = $place_data['name'] ?? '';
    if (!$name) return null;
    
    $loc = $place_data['geometry']['location'] ?? [];
    $lat = $loc['lat'] ?? null;
    $lng = $loc['lng'] ?? null;
    $types = $place_data['types'] ?? [];
    
    // Determine primary category from Google types
    $category = $this->get_place_category($types);
    
    // Check if place already exists
    $existing = get_posts([
      'post_type' => 'local_place',
      'meta_query' => [
        'relation' => 'AND',
        ['key' => 'name', 'value' => $name, 'compare' => '='],
        ['key' => 'latitude', 'value' => $lat, 'compare' => '='],
        ['key' => 'longitude', 'value' => $lng, 'compare' => '=']
      ],
      'posts_per_page' => 1
    ]);
    
    if (!empty($existing)) {
      return $existing[0]->ID; // Return existing ID
    }
    
    // Create new local_place post
    $post_data = [
      'post_title' => $name,
      'post_type' => 'local_place',
      'post_status' => 'publish',
      'post_content' => $place_data['editorial_summary']['overview'] ?? ''
    ];
    
    $post_id = wp_insert_post($post_data);
    if (is_wp_error($post_id)) return null;
    
    // Add metadata
    $meta_fields = [
      'name' => $name,
      'latitude' => $lat,
      'longitude' => $lng,
      'category' => $category,
      'google_place_id' => $place_data['place_id'] ?? '',
      'google_types' => implode(',', $types),
      'address' => $place_data['formatted_address'] ?? '',
      'phone' => $place_data['formatted_phone_number'] ?? '',
      'website' => $place_data['website'] ?? '',
      'rating' => $place_data['rating'] ?? 0,
      'price_level' => $place_data['price_level'] ?? 0,
      'opening_hours' => json_encode($place_data['opening_hours'] ?? [])
    ];
    
    foreach ($meta_fields as $key => $value) {
      if ($value !== null && $value !== '') {
        update_post_meta($post_id, $key, $value);
      }
    }
    
    return $post_id;
  }

  private function get_place_category(array $types): string {
    $category_mapping = [
      'restaurant' => 'Restaurant',
      'food' => 'Restaurant', 
      'bar' => 'Restaurant',
      'cafe' => 'Restaurant',
      'store' => 'Retail',
      'shopping_mall' => 'Retail',
      'gas_station' => 'Services',
      'bank' => 'Services',
      'hospital' => 'Healthcare',
      'pharmacy' => 'Healthcare',
      'school' => 'Education',
      'university' => 'Education',
      'tourist_attraction' => 'Attraction',
      'park' => 'Recreation',
      'gym' => 'Recreation',
      'movie_theater' => 'Entertainment',
      'lodging' => 'Lodging',
      'church' => 'Religious',
      'government' => 'Government'
    ];
    
    foreach ($types as $type) {
      if (isset($category_mapping[$type])) {
        return $category_mapping[$type];
      }
    }
    
    return 'Other';
  }

  private function import_local_place_to_agent(array $place_data): ?int {
    $name = $place_data['name'] ?? '';
    if (!$name) return null;
    
    // Create hpl_ingest post for AI processing
    $post_data = [
      'post_title' => 'Local Place: ' . $name,
      'post_type' => 'hpl_ingest',
      'post_status' => 'publish',
      'post_content' => json_encode($place_data, JSON_PRETTY_PRINT)
    ];
    
    $post_id = wp_insert_post($post_data);
    if (is_wp_error($post_id)) return null;
    
    // Set initial stage
    update_post_meta($post_id, '_hpl_stage', 'new');
    update_post_meta($post_id, '_hpl_source_type', 'google_places');
    update_post_meta($post_id, '_hpl_target_type', 'local_place');
    
    // Store the raw place data
    update_post_meta($post_id, '_hpl_raw_data', $place_data);
    
    return $post_id;
  }

  /**
   * Get list of already imported cities to avoid duplicates
   */
  private function get_existing_cities(): array {
    $cities = get_posts([
      'post_type' => 'city',
      'post_status' => 'publish',
      'numberposts' => -1,
      'fields' => 'ids'
    ]);
    
    $existing = [];
    foreach ($cities as $city_id) {
      $title = get_the_title($city_id);
      $lat = get_post_meta($city_id, 'lat', true);
      $lng = get_post_meta($city_id, 'lng', true);
      
      if ($title) {
        $existing[] = [
          'name' => $title,
          'slug' => sanitize_title($title),
          'lat' => $lat,
          'lng' => $lng
        ];
      }
    }
    
    return $existing;
  }

  /**
   * Enhanced search with multiple strategies and pagination
   */
  private function fetch_with_strategy($google, $conf, $assoc, $strategy, $offset, $limit, $existing_cities) {
    switch ($strategy) {
      case 'bounds':
        return $this->fetch_by_bounds($google, $conf, $limit, $offset);
        
      case 'text':
        return $this->fetch_by_text_search($google, $assoc, $limit, $offset);
        
      case 'county':
        return $this->fetch_by_counties($google, $conf, $limit, $offset);
        
      case 'radius':
        return $this->fetch_by_radius_expansion($google, $conf, $limit, $offset, $existing_cities);
        
      default:
        \WP_CLI::error("Unknown strategy: {$strategy}. Use: bounds, text, county, or radius");
    }
  }

  /**
   * Fetch using geographic bounds (original method)
   */
  private function fetch_by_bounds($google, $conf, $limit, $offset) {
    $bounds = $conf['google']['bounds'] ?? null;
    $types  = $conf['google']['types']  ?? ['locality'];
    
    if (!$bounds) {
      \WP_CLI::error('Missing google.bounds in config.');
    }
    
    \WP_CLI::log("Strategy: Geographic bounds search");
    return $google->nearbyByBounds(
      $bounds['sw'][0], $bounds['sw'][1], 
      $bounds['ne'][0], $bounds['ne'][1], 
      $types,
      $limit + $offset  // Get extra to account for offset
    );
  }

  /**
   * Fetch using varied text searches
   */
  private function fetch_by_text_search($google, $assoc, $limit, $offset) {
    $state = $assoc['state'] ?? 'DE';
    
    $search_queries = [
      "cities in {$state}",
      "towns in {$state}", 
      "municipalities in {$state}",
      "communities in {$state}",
      "incorporated places in {$state}",
      "populated places in {$state}",
      "{$state} localities"
    ];
    
    // Use offset to cycle through different query types
    $query_index = floor($offset / 20) % count($search_queries);
    $query = $search_queries[$query_index];
    
    \WP_CLI::log("Strategy: Text search with query '{$query}' (batch " . ($query_index + 1) . ")");
    
    return $google->textSearch($query);
  }

  /**
   * Fetch by searching county by county
   */
  private function fetch_by_counties($google, $conf, $limit, $offset) {
    $state = $conf['defaults']['state'] ?? 'DE';
    
    // Delaware counties (expand this for other states)
    $counties = [
      'New Castle County',
      'Kent County', 
      'Sussex County'
    ];
    
    $county_index = floor($offset / 30) % count($counties);
    $county = $counties[$county_index];
    
    \WP_CLI::log("Strategy: County-based search in {$county}, {$state}");
    
    $queries = [
      "cities in {$county}, {$state}",
      "towns in {$county}, {$state}",
      "communities in {$county}, {$state}"
    ];
    
    $all_results = [];
    foreach ($queries as $query) {
      $results = $google->textSearch($query);
      if (!is_wp_error($results) && $results) {
        $all_results = array_merge($all_results, $results);
      }
    }
    
    return $all_results;
  }

  /**
   * Fetch by expanding search radius from existing cities
   */
  private function fetch_by_radius_expansion($google, $conf, $limit, $offset, $existing_cities) {
    if (empty($existing_cities)) {
      \WP_CLI::warning("No existing cities found for radius expansion. Use bounds or text strategy first.");
      return [];
    }
    
    \WP_CLI::log("Strategy: Radius expansion from existing cities");
    
    // Pick a city to expand from based on offset
    $base_city_index = $offset % count($existing_cities);
    $base_city = $existing_cities[$base_city_index];
    
    if (empty($base_city['lat']) || empty($base_city['lng'])) {
      \WP_CLI::warning("Base city {$base_city['name']} has no coordinates");
      return [];
    }
    
    \WP_CLI::log("Expanding search from: {$base_city['name']}");
    
    // Search in expanding radius
    $radius = 15000 + ($offset * 1000); // Start at 15km, expand by 1km per offset
    $results = $google->nearbySearchAtPoint($base_city['lat'], $base_city['lng'], 'locality', $radius);
    
    return is_wp_error($results) ? [] : $results;
  }

  /**
   * Check if a city is already imported
   */
  private function is_city_duplicate($city_data, $existing_cities) {
    $name = $city_data['name'] ?? '';
    $lat = $city_data['geometry']['location']['lat'] ?? null;
    $lng = $city_data['geometry']['location']['lng'] ?? null;
    
    if (!$name) return true; // Skip unnamed places
    
    foreach ($existing_cities as $existing) {
      // Check by name similarity
      if (strtolower($existing['name']) === strtolower($name)) {
        return true;
      }
      
      // Check by proximity (within 2km)
      if ($lat && $lng && $existing['lat'] && $existing['lng']) {
        $distance = $this->calculate_distance($lat, $lng, $existing['lat'], $existing['lng']);
        if ($distance < 2) { // Within 2km
          \WP_CLI::log("Skipping {$name} - too close to existing {$existing['name']} ({$distance}km)");
          return true;
        }
      }
    }
    
    return false;
  }
  
  /**
   * Calculate distance between two points in kilometers
   */
  private function calculate_distance($lat1, $lng1, $lat2, $lng2) {
    $earth_radius = 6371;
    
    $lat_delta = deg2rad($lat2 - $lat1);
    $lng_delta = deg2rad($lng2 - $lng1);
    
    $a = sin($lat_delta/2) * sin($lat_delta/2) + 
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
         sin($lng_delta/2) * sin($lng_delta/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earth_radius * $c;
  }
}

\WP_CLI::add_command('hpl:cities', [CitiesFetch::class, 'fetch']);
}
