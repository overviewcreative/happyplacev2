<?php
namespace HappyPlace\Local\Agent\LLM;

class AnthropicAdapter {
  private string $api_key;
  private string $model;
  private string $base_url;

  public function __construct(string $api_key, string $model = 'claude-3-5-sonnet-20241022', string $base_url = 'https://api.anthropic.com/v1') {
    $this->api_key = $api_key;
    $this->model = $model;
    $this->base_url = $base_url;
  }

  public function json_call(array $messages, array $schema): array {
    // Convert OpenAI format messages to Anthropic format
    $system_message = '';
    $formatted_messages = [];
    
    foreach ($messages as $message) {
      if ($message['role'] === 'system') {
        $system_message = $message['content'];
      } else {
        $formatted_messages[] = [
          'role' => $message['role'] === 'assistant' ? 'assistant' : 'user',
          'content' => $message['content']
        ];
      }
    }

    // Add JSON schema instruction to system message
    $schema_instruction = "\nPlease respond with valid JSON matching this schema: " . json_encode($schema);
    $system_message .= $schema_instruction;

    $data = [
      'model' => $this->model,
      'max_tokens' => 4000,
      'temperature' => 0.1,
      'system' => $system_message,
      'messages' => $formatted_messages
    ];

    $response = wp_remote_post($this->base_url . '/messages', [
      'timeout' => 60,
      'headers' => [
        'x-api-key' => $this->api_key,
        'content-type' => 'application/json',
        'anthropic-version' => '2023-06-01',
        'User-Agent' => 'HappyPlaceLocal/1.0'
      ],
      'body' => json_encode($data)
    ]);

    if (is_wp_error($response)) {
      throw new \Exception('Anthropic API request failed: ' . $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    if (!$result || isset($result['error'])) {
      throw new \Exception('Anthropic API error: ' . ($result['error']['message'] ?? 'Unknown error'));
    }

    $content = $result['content'][0]['text'] ?? '';
    
    // Extract JSON from response if wrapped in markdown
    if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/s', $content, $matches)) {
      $content = $matches[1];
    }

    return json_decode($content, true) ?: [];
  }

  public function text_call(array $messages): string {
    // Convert OpenAI format messages to Anthropic format
    $system_message = '';
    $formatted_messages = [];
    
    foreach ($messages as $message) {
      if ($message['role'] === 'system') {
        $system_message = $message['content'];
      } else {
        $formatted_messages[] = [
          'role' => $message['role'] === 'assistant' ? 'assistant' : 'user',
          'content' => $message['content']
        ];
      }
    }

    $data = [
      'model' => $this->model,
      'max_tokens' => 4000,
      'temperature' => 0.7,
      'system' => $system_message,
      'messages' => $formatted_messages
    ];

    $response = wp_remote_post($this->base_url . '/messages', [
      'timeout' => 60,
      'headers' => [
        'x-api-key' => $this->api_key,
        'content-type' => 'application/json',
        'anthropic-version' => '2023-06-01',
        'User-Agent' => 'HappyPlaceLocal/1.0'
      ],
      'body' => json_encode($data)
    ]);

    if (is_wp_error($response)) {
      throw new \Exception('Anthropic API request failed: ' . $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    if (!$result || isset($result['error'])) {
      throw new \Exception('Anthropic API error: ' . ($result['error']['message'] ?? 'Unknown error'));
    }

    return $result['content'][0]['text'] ?? '';
  }
}