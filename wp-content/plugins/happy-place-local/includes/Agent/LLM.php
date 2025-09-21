<?php
namespace HappyPlace\Local\Agent;

class LLM {
  public static function json_call(array $messages, array $schema){
    /**
     * Vendors differ; we expose a filter so projects can plug their own client:
     * add_filter('hpl/llm/json_call', function($null, $messages, $schema){ ...return $array; }, 10, 3);
     */
    $out = apply_filters('hpl/llm/json_call', null, $messages, $schema);
    if (is_null($out)) {
      // Fallback: just return an empty structure to avoid fatal
      return ['_note'=>'No LLM provider wired. Hook hpl/llm/json_call.'];
    }
    return $out;
  }
  public static function text_call(array $messages){
    $out = apply_filters('hpl/llm/text_call', null, $messages);
    if (is_null($out)) return '[[LLM provider not configured]]';
    return $out;
  }
}
