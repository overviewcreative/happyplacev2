<?php
namespace HappyPlace\Local\Agent\LLM;

class CustomAdapter {
  private string $base_url;
  private array $headers;
  private string $model;

  public function __construct(string $base_url, array $headers = [], string $model = 'default') {
    $this->base_url = rtrim($base_url, '/');
    $this->headers = array_merge([
      'Content-Type' => 'application/json',
      'User-Agent' => 'HappyPlaceLocal/1.0'
    ], $headers);
    $this->model = $model;
  }

  public function json_call(array $messages, array $schema): array {
    $data = [
      'model' => $this->model,
      'messages' => $messages,
      'schema' => $schema,
      'format' => 'json',
      'temperature' => 0.1
    ];

    $response = wp_remote_post($this->base_url . '/chat/completions', [
      'timeout' => 60,
      'headers' => $this->headers,
      'body' => json_encode($data)
    ]);

    if (is_wp_error($response)) {
      throw new \Exception('Custom LLM API request failed: ' . $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    if (!$result) {
      throw new \Exception('Custom LLM API returned invalid JSON');
    }

    // Handle different response formats
    if (isset($result['choices'][0]['message']['content'])) {
      // OpenAI-compatible format
      $content = $result['choices'][0]['message']['content'];
    } elseif (isset($result['content'])) {
      // Direct content format
      $content = is_array($result['content']) ? $result['content'][0]['text'] : $result['content'];
    } elseif (isset($result['response'])) {
      // Custom response format
      $content = $result['response'];
    } else {
      throw new \Exception('Custom LLM API response format not recognized');
    }

    // Parse JSON response
    if (is_string($content)) {
      $parsed = json_decode($content, true);
      return $parsed ?: [];
    }

    return is_array($content) ? $content : [];
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
      'headers' => $this->headers,
      'body' => json_encode($data)
    ]);

    if (is_wp_error($response)) {
      throw new \Exception('Custom LLM API request failed: ' . $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    if (!$result) {
      throw new \Exception('Custom LLM API returned invalid JSON');
    }

    // Handle different response formats
    if (isset($result['choices'][0]['message']['content'])) {
      // OpenAI-compatible format
      return $result['choices'][0]['message']['content'];
    } elseif (isset($result['content'])) {
      // Direct content format
      return is_array($result['content']) ? $result['content'][0]['text'] : $result['content'];
    } elseif (isset($result['response'])) {
      // Custom response format
      return $result['response'];
    } else {
      throw new \Exception('Custom LLM API response format not recognized');
    }
  }
}