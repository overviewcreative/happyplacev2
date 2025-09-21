<?php
namespace HappyPlace\Local\Services;

class GooglePlacesClient {
  private $api_key;
  private $base_url = 'https://maps.googleapis.com/maps/api/place/';
  
  public function __construct($api_key = null) {
    if ($api_key) {
      $this->api_key = $api_key;
    } else {
      // Load from settings
      $settings = get_option('hpl_config', []);
      $this->api_key = $settings['google_places_api_key'] ?? '';
    }
  }

  public function textSearch($query, $bounds = null) {
    if (empty($this->api_key)) {
      return new \WP_Error('no_api_key', 'Google Places API key not configured');
    }

    $params = [
      'query' => sanitize_text_field($query),
      'key' => $this->api_key
    ];
    
    $response = wp_remote_get($this->base_url . 'textsearch/json?' . http_build_query($params));
    
    if (is_wp_error($response)) {
      return $response;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if ($data['status'] !== 'OK' && $data['status'] !== 'ZERO_RESULTS') {
      return new \WP_Error('api_error', 'Google Places API error: ' . $data['status']);
    }
    
    return $data['results'] ?? [];
  }

  public function nearbyByBounds($sw_lat, $sw_lng, $ne_lat, $ne_lng, $types = ['establishment'], $limit = 20) {
    if (empty($this->api_key)) {
      return new \WP_Error('no_api_key', 'Google Places API key not configured');
    }

    // Use the enhanced search method that prioritizes ratings
    return $this->searchHighRatedPlacesByBounds($sw_lat, $sw_lng, $ne_lat, $ne_lng, $types, $limit);
  }

  public function searchHighRatedPlacesByBounds($sw_lat, $sw_lng, $ne_lat, $ne_lng, $types = ['establishment'], $limit = 20) {
    if (empty($this->api_key)) {
      return new \WP_Error('no_api_key', 'Google Places API key not configured');
    }

    $all_results = [];
    
    // Search multiple points across the region to get better coverage
    $search_points = $this->generateSearchPoints($sw_lat, $sw_lng, $ne_lat, $ne_lng);
    
    // Calculate a conservative radius based on Delaware's dimensions
    $delaware_radius = $this->calculateOptimalRadius($sw_lat, $sw_lng, $ne_lat, $ne_lng);
    
    foreach ($search_points as $point) {
      $results = $this->nearbySearchAtPoint($point['lat'], $point['lng'], $types[0], $delaware_radius);
      if (!is_wp_error($results)) {
        $all_results = array_merge($all_results, $results);
      }
      sleep(0.1); // Brief pause to respect rate limits
    }
    
    // Remove duplicates and filter by bounds
    $filtered_results = $this->filterAndDeduplicateResults($all_results, $sw_lat, $sw_lng, $ne_lat, $ne_lng);
    
    // Sort by rating (highest first), then by review count
    usort($filtered_results, function($a, $b) {
      $rating_a = $a['rating'] ?? 0;
      $rating_b = $b['rating'] ?? 0;
      $reviews_a = $a['user_ratings_total'] ?? 0;
      $reviews_b = $b['user_ratings_total'] ?? 0;
      
      // First compare by rating
      if ($rating_a !== $rating_b) {
        return $rating_b <=> $rating_a;
      }
      
      // If ratings are equal, compare by review count
      return $reviews_b <=> $reviews_a;
    });
    
    return array_slice($filtered_results, 0, $limit);
  }

  private function generateSearchPoints($sw_lat, $sw_lng, $ne_lat, $ne_lng) {
    // Generate a grid of search points across the region
    $lat_step = ($ne_lat - $sw_lat) / 3; // 3x3 grid
    $lng_step = ($ne_lng - $sw_lng) / 3;
    
    $points = [];
    for ($i = 0; $i < 3; $i++) {
      for ($j = 0; $j < 3; $j++) {
        $points[] = [
          'lat' => $sw_lat + ($lat_step * $i) + ($lat_step / 2),
          'lng' => $sw_lng + ($lng_step * $j) + ($lng_step / 2)
        ];
      }
    }
    
    return $points;
  }

  private function calculateOptimalRadius($sw_lat, $sw_lng, $ne_lat, $ne_lng) {
    // Calculate the distance from center to corner of Delaware bounds
    $center_lat = ($sw_lat + $ne_lat) / 2;
    $center_lng = ($sw_lng + $ne_lng) / 2;
    
    // Distance from center to northeast corner (longest distance)
    $distance_to_corner = $this->calculateDistance($center_lat, $center_lng, $ne_lat, $ne_lng);
    
    // Use 75% of that distance to ensure we stay well within Delaware
    // Convert to meters and cap at 30km to be extra conservative
    $optimal_radius = min($distance_to_corner * 0.75 * 1000, 30000);
    
    return (int) $optimal_radius;
  }

  private function calculateDistance($lat1, $lng1, $lat2, $lng2) {
    // Haversine formula to calculate distance in kilometers
    $earth_radius = 6371;
    
    $lat_delta = deg2rad($lat2 - $lat1);
    $lng_delta = deg2rad($lng2 - $lng1);
    
    $a = sin($lat_delta/2) * sin($lat_delta/2) + 
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
         sin($lng_delta/2) * sin($lng_delta/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earth_radius * $c;
  }

  private function nearbySearchAtPoint($lat, $lng, $type, $radius = 25000) {
    $params = [
      'location' => $lat . ',' . $lng,
      'radius' => $radius,
      'type' => $type,
      'key' => $this->api_key
    ];
    
    $response = wp_remote_get($this->base_url . 'nearbysearch/json?' . http_build_query($params));
    
    if (is_wp_error($response)) {
      return $response;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if ($data['status'] !== 'OK' && $data['status'] !== 'ZERO_RESULTS') {
      return new \WP_Error('api_error', 'Google Places API error: ' . $data['status']);
    }
    
    return $data['results'] ?? [];
  }

  private function filterAndDeduplicateResults($results, $sw_lat, $sw_lng, $ne_lat, $ne_lng) {
    $seen_places = [];
    $filtered = [];
    
    // Add a small buffer inside Delaware's borders to be extra safe
    $lat_buffer = ($ne_lat - $sw_lat) * 0.02; // 2% buffer
    $lng_buffer = ($ne_lng - $sw_lng) * 0.02; // 2% buffer
    
    $safe_sw_lat = $sw_lat + $lat_buffer;
    $safe_sw_lng = $sw_lng + $lng_buffer;
    $safe_ne_lat = $ne_lat - $lat_buffer;
    $safe_ne_lng = $ne_lng - $lng_buffer;
    
    foreach ($results as $result) {
      $place_id = $result['place_id'] ?? '';
      if (empty($place_id) || isset($seen_places[$place_id])) {
        continue; // Skip duplicates
      }
      
      // Check if within conservative bounds (slightly inside Delaware)
      $lat = $result['geometry']['location']['lat'] ?? null;
      $lng = $result['geometry']['location']['lng'] ?? null;
      
      if ($lat && $lng && 
          $lat >= $safe_sw_lat && $lat <= $safe_ne_lat && 
          $lng >= $safe_sw_lng && $lng <= $safe_ne_lng) {
        
        $seen_places[$place_id] = true;
        $filtered[] = $result;
      }
    }
    
    return $filtered;
  }

  public function details($place_id) {
    if (empty($this->api_key)) {
      return new \WP_Error('no_api_key', 'Google Places API key not configured');
    }

    $params = [
      'place_id' => sanitize_text_field($place_id),
      'fields' => 'name,formatted_address,geometry,formatted_phone_number,international_phone_number,website,opening_hours,rating,user_ratings_total,types,photos',
      'key' => $this->api_key
    ];
    
    $response = wp_remote_get($this->base_url . 'details/json?' . http_build_query($params));
    
    if (is_wp_error($response)) {
      return $response;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if ($data['status'] !== 'OK') {
      return new \WP_Error('api_error', 'Google Places API error: ' . $data['status']);
    }
    
    return $data['result'] ?? [];
  }
}
