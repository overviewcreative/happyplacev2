<?php
namespace HappyPlace\Local\Agent\Tasks;

use HappyPlace\Local\Agent\IngestStore;

class Enrich {
  public static function handle(int $post_id){
    try {
      if ($post_id <= 0) {
        error_log('[HPL] Enrich: invalid post_id: ' . $post_id);
        return;
      }
      
      $data = IngestStore::read($post_id) ?: [];

      // Only for places; let others pass through.
      $target = get_post_meta($post_id, '_hpl_target_type', true) ?: 'local_place';
      if ($target !== 'local_place') {
        // Non-places skip enrichment
        IngestStore::stage($post_id, 'enriched');
        error_log('[HPL] Enrich: skipping non-place target type "' . $target . '" for #' . $post_id);
        return;
      }

      // Check if this looks like a city/locality and not an establishment
      $types = isset($data['types']) && is_array($data['types']) ? $data['types'] : [];
      $looks_city = array_intersect($types, ['locality', 'postal_town', 'administrative_area_level_3', 'political']);
      $is_establishment = in_array('establishment', $types, true);
      
      if (!empty($looks_city) && !$is_establishment) {
        update_post_meta($post_id, '_hpl_error', 'Locality/city data detected, not a place');
        IngestStore::stage($post_id, 'ready_for_review');
        error_log('[HPL] Enrich: skipping city-like result for #' . $post_id);
        return;
      }
      $placeId = isset($data['place_id']) ? $data['place_id'] : (isset($data['reference']) ? $data['reference'] : null);
      $hasCore = !empty($data['formatted_address']) && !empty($data['website']) && !empty($data['opening_hours']);

      // Require place_id for enrichment
      if (!$placeId) {
        update_post_meta($post_id, '_hpl_error', 'No place_id available for enrichment');
        IngestStore::stage($post_id, 'enriched'); // Skip enrichment but continue pipeline
        error_log('[HPL] Enrich: no place_id for #' . $post_id);
        return;
      }

      if (!$hasCore) {
        // Load API key - prefer constants, then options, then JSON config
        $apiKey = '';
        if (defined('HPL_GOOGLE_PLACES_KEY')) {
          $apiKey = HPL_GOOGLE_PLACES_KEY;
        } else {
          $cfg = get_option('hpl_config', []);
          $apiKey = isset($cfg['google_places_api_key']) ? trim($cfg['google_places_api_key']) : '';
          if (!$apiKey) {
            // fallback to file if you use it
            $file = WP_CONTENT_DIR . '/plugins/happy-place-local/config/sources.local.json';
            if (file_exists($file)) {
              $json = json_decode(file_get_contents($file), true);
              if ($json && is_array($json)) {
                $apiKey = isset($json['google']['api_key']) ? trim($json['google']['api_key']) : '';
              }
            }
          }
        }

        if (!$apiKey) {
          update_post_meta($post_id, '_hpl_error', 'No Google Places API key configured');
          IngestStore::stage($post_id, 'enriched'); // Skip enrichment but continue
          error_log('[HPL] Enrich: no API key for #' . $post_id);
          return;
        }

        try {
          $client = new \HappyPlace\Local\Services\GooglePlacesClient($apiKey);
          $details = $client->details($placeId);
          
          // Check if details is a WP_Error
          if (is_wp_error($details)) {
            update_post_meta($post_id, '_hpl_error', 'Google Places API error: ' . $details->get_error_message());
            IngestStore::stage($post_id, 'enriched');
            error_log('[HPL] Enrich: API error for #' . $post_id . ': ' . $details->get_error_message());
            return;
          }
          
          if (!$details || !is_array($details)) {
            update_post_meta($post_id, '_hpl_error', 'Failed to fetch place details from Google');
            IngestStore::stage($post_id, 'enriched');
            error_log('[HPL] Enrich: empty details response for #' . $post_id . ' place_id: ' . $placeId);
            return;
          }

          // Merge useful fields - defensive array access
          $mergeKeys = [
            'name','formatted_address','international_phone_number','formatted_phone_number',
            'website','opening_hours','rating','user_ratings_total','types','geometry','photos'
          ];
          foreach ($mergeKeys as $k) {
            if (isset($details[$k]) && $details[$k] !== null && $details[$k] !== '') {
              $data[$k] = $details[$k];
            }
          }

          // Normalize phone key your scorer expects
          if (!empty($data['international_phone_number']) && empty($data['formatted_phone_number'])) {
            $data['formatted_phone_number'] = $data['international_phone_number'];
          }

          // Optional: photo → simple URL - defensive array access
          if (isset($details['photos'][0]['photo_reference']) && empty($data['image_url'])) {
            $photoRef = $details['photos'][0]['photo_reference'];
            $data['image_url'] = add_query_arg([
              'maxwidth' => 1200,
              'photoreference' => $photoRef,
              'key' => $apiKey
            ], 'https://maps.googleapis.com/maps/api/place/photo');
          }

          // Decide/record target type
          update_post_meta($post_id, '_hpl_target_type', 'local_place');
        } catch (\Throwable $e) {
          update_post_meta($post_id, '_hpl_error', 'Google Places API error: ' . $e->getMessage());
          error_log('[HPL] Enrich: API error for #' . $post_id . ': ' . $e->getMessage());
          // Continue pipeline even on API failure
        }
      }

      // Derive primary_category from Google types if classifier didn't set it
      $class = IngestStore::read_meta($post_id, '_hpl_classify') ?: [];
      if (empty($class['primary_category']) && !empty($data['types']) && is_array($data['types'])) {
        $map = [
          'restaurant' => 'Restaurant',
          'bar' => 'Bar',
          'cafe' => 'Cafe',
          'bakery' => 'Bakery',
          'meal_takeaway' => 'Takeout',
          'meal_delivery' => 'Delivery'
        ];
        foreach ($data['types'] as $t) {
          if (is_string($t) && isset($map[$t])) { 
            $class['primary_category'] = $map[$t]; 
            break; 
          }
        }
        if (!empty($class)) {
          IngestStore::write($post_id, '_hpl_classify', $class);
        }
      }

      // Save and advance
      IngestStore::write($post_id, '_hpl_raw', $data);
      do_action('hpl/agent/enrich_item', $post_id, $data);
      IngestStore::stage($post_id, 'enriched');
    } catch (\Throwable $e) {
      update_post_meta($post_id, '_hpl_error', 'Enrich failed: ' . $e->getMessage());
      error_log('[HPL] Enrich: fatal error for #' . $post_id . ': ' . $e->getMessage());
      // Still advance stage to prevent getting stuck
      IngestStore::stage($post_id, 'enriched');
    }
  }
}
