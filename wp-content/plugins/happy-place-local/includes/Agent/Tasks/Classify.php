<?php
namespace HappyPlace\Local\Agent\Tasks;

use HappyPlace\Local\Agent\IngestStore;
use HappyPlace\Local\Agent\LLM;

class Classify {
  public static function handle(int $post_id){
    $payload = IngestStore::read($post_id);
    $target_type = get_post_meta($post_id, '_hpl_target_type', true);
    
    if ($target_type === 'local_place') {
      // Schema for local places (restaurants, attractions, etc.)
      $schema = [
        'type'=>'object',
        'properties'=>[
          'primary_category'=>['type'=>'string'],
          'secondary_categories'=>['type'=>'array','items'=>['type'=>'string']],
          'tags'=>['type'=>'array','items'=>['type'=>'string']],
          'price_range'=>['type'=>'string','enum'=>['$','$$','$$$','$$$$']],
          'kid_friendly'=>['type'=>'boolean'],
          'outdoor_seating'=>['type'=>'boolean'],
          'parking_available'=>['type'=>'boolean'],
          'wheelchair_accessible'=>['type'=>'boolean'],
          'confidence'=>['type'=>'number','minimum'=>0,'maximum'=>1]
        ],
        'required'=>['primary_category','confidence']
      ];
      $system_prompt = 'You classify local places like restaurants, attractions, and businesses. Extract key attributes from Google Places data. Focus on customer-relevant features. Return strict JSON.';
    } else {
      // Default schema for other content
      $schema = [
        'type'=>'object',
        'properties'=>[
          'city_slug'=>['type'=>'string'],
          'places'=>['type'=>'array','items'=>['type'=>'string']],
          'tags'=>['type'=>'array','items'=>['type'=>'string']],
          'is_free'=>['type'=>'boolean'],
          'confidence'=>['type'=>'number']
        ],
        'required'=>['city_slug','confidence']
      ];
      $system_prompt = 'You classify local items. Return strict JSON.';
    }
    
    $out = LLM::json_call([
      ['role'=>'system','content'=>$system_prompt],
      ['role'=>'user','content'=> wp_json_encode($payload) ]
    ], $schema);

    IngestStore::write($post_id, '_hpl_classify', $out);
    IngestStore::stage($post_id, 'classified');
  }
}
