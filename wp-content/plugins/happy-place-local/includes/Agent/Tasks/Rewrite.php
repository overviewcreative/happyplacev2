<?php
namespace HappyPlace\Local\Agent\Tasks;

use HappyPlace\Local\Agent\IngestStore;
use HappyPlace\Local\Agent\LLM;

class Rewrite {
  public static function handle(int $post_id){
    $payload = IngestStore::read($post_id);
    $target_type = get_post_meta($post_id, '_hpl_target_type', true);
    $classification = IngestStore::read_meta($post_id, '_hpl_classify');
    
    if ($target_type === 'local_place') {
      $primary_category = $classification['primary_category'] ?? 'Restaurant';
      $tags = !empty($classification['tags']) ? implode(', ', $classification['tags']) : '';
      
      $system_prompt = "Write a warm, local-friendly description for this Delaware {$primary_category}. 
Voice: welcoming, informative, conversational. 
Focus on: what makes this place special, atmosphere, key offerings, local appeal.
Include practical details like location and contact info.
Output: clean markdown, 80-150 words. No hype or superlatives.
Tags to consider: {$tags}";
    } else {
      $system_prompt = 'Voice: hometown-casual, warm, clear. Output concise markdown (<=120 words). No hype. Keep attribution.';
    }
    
    $text = LLM::text_call([
      ['role'=>'system','content'=>$system_prompt],
      ['role'=>'user','content'=> wp_json_encode($payload) ]
    ]);
    IngestStore::write($post_id, '_hpl_rewrite_md', $text);
    IngestStore::stage($post_id, 'rewritten');
  }
}
