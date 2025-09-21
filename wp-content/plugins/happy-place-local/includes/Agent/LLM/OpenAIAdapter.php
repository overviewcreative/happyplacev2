<?php
namespace HappyPlace\Local\Agent\LLM;

class OpenAIAdapter {
  private string $api_key;
  private string $model;
  private string $base_url;

  public function __construct(string $api_key, string $model = 'gpt-4', string $base_url = 'https://api.openai.com/v1') {
    $this->api_key = $api_key;
    $this->model = $model;
    $this->base_url = $base_url;
  }

  public function json_call(array $messages, array $schema): array {
    $data = [
      'model' => $this->model,
      'messages' => $messages,
      'response_format' => [
        'type' => 'json_schema',
        'json_schema' => [
          'name' => 'response',
          'schema' => $schema
        ]
      ],
      'temperature' => 0.1
    ];

    $response = wp_remote_post($this->base_url . '/chat/completions', [
      'timeout' => 60,
      'headers' => [
        'Authorization' => 'Bearer ' . $this->api_key,
        'Content-Type' => 'application/json',
        'User-Agent' => 'HappyPlaceLocal/1.0'
      ],
      'body' => json_encode($data)
    ]);

    if (is_wp_error($response)) {
      throw new \Exception('OpenAI API request failed: ' . $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    if (!$result || isset($result['error'])) {
      throw new \Exception('OpenAI API error: ' . ($result['error']['message'] ?? 'Unknown error'));
    }

    $content = $result['choices'][0]['message']['content'] ?? '';
    return json_decode($content, true) ?: [];
  }

  public function text_call(array $messages): string {
    $data = [
      'model' => $this->model,
      'messages' => $messages,
      'temperature' => 0.7,
      'max_tokens' => 2000
    ];

    $response = wp_remote_post($this->base_url . '/chat/completions', [
      'timeout' => 60,
      'headers' => [
        'Authorization' => 'Bearer ' . $this->api_key,
        'Content-Type' => 'application/json',
        'User-Agent' => 'HappyPlaceLocal/1.0'
      ],
      'body' => json_encode($data)
    ]);

    if (is_wp_error($response)) {
      throw new \Exception('OpenAI API request failed: ' . $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    if (!$result || isset($result['error'])) {
      throw new \Exception('OpenAI API error: ' . ($result['error']['message'] ?? 'Unknown error'));
    }

    return $result['choices'][0]['message']['content'] ?? '';
  }
}