<?php
namespace HappyPlace\Local\Agent;

use HappyPlace\Local\Agent\LLM\OpenAIAdapter;
use HappyPlace\Local\Agent\LLM\AnthropicAdapter;
use HappyPlace\Local\Agent\LLM\CustomAdapter;

class LLMManager {
  private static ?object $adapter = null;

  public static function init(): void {
    add_filter('hpl/llm/json_call', [self::class, 'handle_json_call'], 10, 3);
    add_filter('hpl/llm/text_call', [self::class, 'handle_text_call'], 10, 2);
  }

  public static function handle_json_call($null, array $messages, array $schema): ?array {
    $adapter = self::get_adapter();
    if (!$adapter) {
      return null;
    }

    try {
      return $adapter->json_call($messages, $schema);
    } catch (\Exception $e) {
      error_log('LLM JSON call failed: ' . $e->getMessage());
      return ['error' => $e->getMessage()];
    }
  }

  public static function handle_text_call($null, array $messages): ?string {
    $adapter = self::get_adapter();
    if (!$adapter) {
      return null;
    }

    try {
      return $adapter->text_call($messages);
    } catch (\Exception $e) {
      error_log('LLM text call failed: ' . $e->getMessage());
      return 'Error: ' . $e->getMessage();
    }
  }

  private static function get_adapter(): ?object {
    if (self::$adapter !== null) {
      return self::$adapter;
    }

    $config = get_option('hpl_config', []);
    $provider = $config['llm_provider'] ?? '';

    switch ($provider) {
      case 'openai':
        $api_key = $config['openai_api_key'] ?? '';
        $model = $config['llm_model'] ?? 'gpt-4';
        $base_url = $config['openai_base_url'] ?? 'https://api.openai.com/v1';
        
        if (!$api_key) {
          error_log('OpenAI API key not configured');
          return null;
        }

        self::$adapter = new OpenAIAdapter($api_key, $model, $base_url);
        break;

      case 'anthropic':
        $api_key = $config['anthropic_api_key'] ?? '';
        $model = $config['llm_model'] ?? 'claude-3-5-sonnet-20241022';
        
        if (!$api_key) {
          error_log('Anthropic API key not configured');
          return null;
        }

        self::$adapter = new AnthropicAdapter($api_key, $model);
        break;

      case 'custom':
        $base_url = $config['custom_base_url'] ?? '';
        $api_key = $config['custom_api_key'] ?? '';
        $model = $config['custom_model'] ?? 'default';
        
        if (!$base_url) {
          error_log('Custom LLM base URL not configured');
          return null;
        }

        $headers = [];
        if ($api_key) {
          $auth_header = $config['custom_auth_header'] ?? 'Authorization';
          $auth_format = $config['custom_auth_format'] ?? 'Bearer %s';
          $headers[$auth_header] = sprintf($auth_format, $api_key);
        }

        self::$adapter = new CustomAdapter($base_url, $headers, $model);
        break;

      default:
        error_log('No LLM provider configured');
        return null;
    }

    return self::$adapter;
  }

  public static function test_connection(): array {
    $adapter = self::get_adapter();
    if (!$adapter) {
      return [
        'success' => false,
        'message' => 'No LLM provider configured'
      ];
    }

    try {
      $response = $adapter->text_call([
        ['role' => 'user', 'content' => 'Hello! Please respond with "Connection successful" if you can read this.']
      ]);

      return [
        'success' => true,
        'message' => 'Connection successful',
        'response' => $response
      ];
    } catch (\Exception $e) {
      return [
        'success' => false,
        'message' => 'Connection failed: ' . $e->getMessage()
      ];
    }
  }

  public static function reset_adapter(): void {
    self::$adapter = null;
  }
}