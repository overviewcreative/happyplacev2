<?php
namespace HappyPlace\Local\Agent\Tasks;

use HappyPlace\Local\Agent\IngestStore;

class Score {
  public static function handle(int $post_id){
    try {
      if ($post_id <= 0) {
        error_log('[HPL] Score: invalid post_id: ' . $post_id);
        return;
      }
      
      $score = 0;
      $data = IngestStore::read($post_id);
      $classification = IngestStore::read_meta($post_id, '_hpl_classify');
      $target_type = get_post_meta($post_id, '_hpl_target_type', true);
    
    if ($target_type === 'local_place') {
      // Scoring system for local places
      $score = 40; // Base score
      
      // Google Places data quality
      if (!empty($data['rating']) && $data['rating'] >= 4.0) $score += 20;
      if (!empty($data['user_ratings_total']) && $data['user_ratings_total'] >= 10) $score += 15;
      if (!empty($data['formatted_address'])) $score += 10;
      
      // Phone number - check both formatted versions
      if (!empty($data['formatted_phone_number']) || !empty($data['international_phone_number'])) {
        $score += 5;
      }
      
      if (!empty($data['website'])) $score += 5;
      if (!empty($data['opening_hours'])) $score += 5;
      
      // AI classification confidence
      $confidence = $classification['confidence'] ?? 0;
      $score += (int) ($confidence * 20); // 0-20 points based on confidence
      
      // Content quality (if rewritten) - allow shorter rewrites to contribute
      $rewritten = get_post_meta($post_id, '_hpl_rewrite_md', true);
      if (!empty($rewritten)) {
        $length = strlen(wp_strip_all_tags($rewritten));
        if ($length > 140) {
          $score += 10; // Full points for longer content
        } elseif ($length > 80) {
          $score += 8; // Partial points for medium content
        }
      }
      
      // Bonus for complete data
      $has_complete_data = !empty($data['name']) && 
                          !empty($data['geometry']['location']['lat']) && 
                          !empty($data['geometry']['location']['lng']) &&
                          !empty($classification['primary_category']);
      if ($has_complete_data) $score += 10;
      
    } else {
      // Default scoring for events (original logic)
      $has_img = !empty($data['image_url'] ?? '');
      $is_weekend = !empty($data['start']) ? (date('N', strtotime($data['start'])) >= 5) : false;
      $score += $has_img ? 20 : 0;
      $score += $is_weekend ? 30 : 10;
    }
    
    // Cap at 100
    $score = min($score, 100);
    
    // Allow score to be filtered and check configurable threshold
    $score = apply_filters('hpl/agent/score', $score, $post_id, $data);
    
    // Save score
    IngestStore::write($post_id, '_hpl_score', $score);
    IngestStore::stage($post_id, 'scored');
    
      // Log score for debugging
      error_log('[HPL] Score: #' . $post_id . ' scored ' . $score . '/100');
    } catch (\Throwable $e) {
      error_log('[HPL] Score: fatal error for #' . $post_id . ': ' . $e->getMessage());
      update_post_meta($post_id, '_hpl_error', 'Scoring failed: ' . $e->getMessage());
      // Still advance stage to prevent getting stuck
      IngestStore::stage($post_id, 'scored');
    }
  }
}
