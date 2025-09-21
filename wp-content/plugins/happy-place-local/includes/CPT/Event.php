<?php
namespace HappyPlace\Local\CPT;

class Event {
  const TYPE = 'local_event';
  
  public static function register() {
    register_post_type(self::TYPE, [
      'labels' => [
        'name' => __('Local Events', 'happy-place-local'),
        'singular_name' => __('Local Event', 'happy-place-local'),
        'add_new' => __('Add New', 'happy-place-local'),
        'add_new_item' => __('Add New Local Event', 'happy-place-local'),
        'edit_item' => __('Edit Local Event', 'happy-place-local'),
        'new_item' => __('New Local Event', 'happy-place-local'),
        'view_item' => __('View Local Event', 'happy-place-local'),
        'search_items' => __('Search Local Events', 'happy-place-local'),
        'not_found' => __('No local events found', 'happy-place-local'),
        'not_found_in_trash' => __('No local events found in trash', 'happy-place-local'),
      ],
      'description' => __('Local events and activities', 'happy-place-local'),
      'public' => true,
      'publicly_queryable' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'query_var' => true,
      'rewrite' => ['slug' => 'events', 'with_front' => false],
      'capability_type' => 'post',
      'has_archive' => true,
      'hierarchical' => false,
      'menu_position' => 26,
      'menu_icon' => 'dashicons-calendar-alt',
      'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'],
      'show_in_rest' => true,
      'rest_base' => 'local_events',
    ]);

    // Register meta fields for REST API
    self::register_meta_fields();
  }

  private static function register_meta_fields() {
    $meta_fields = [
      'start_datetime' => 'string',
      'end_datetime' => 'string',
      'price' => 'string',
      'source_url' => 'string',
      'hpl_city_slug' => 'string',
      'hpl_city_id' => 'integer',
      'is_free' => 'boolean',
      'age_min' => 'integer',
      'tickets_url' => 'string',
      'organizer_name' => 'string',
      'venue_name' => 'string',
      'venue_address' => 'string',
      'lat' => 'number',
      'lng' => 'number',
      'attribution' => 'string',
    ];

    foreach ($meta_fields as $key => $type) {
      register_post_meta(self::TYPE, $key, [
        'type' => $type,
        'single' => true,
        'sanitize_callback' => self::get_sanitize_callback($type),
        'show_in_rest' => true,
        'auth_callback' => function() {
          return current_user_can('edit_posts');
        }
      ]);
    }
  }

  private static function get_sanitize_callback($type) {
    switch ($type) {
      case 'integer': return 'absint';
      case 'number': return function($value) { return floatval($value); };
      case 'boolean': return function($value) { return (bool) $value; };
      default: return 'sanitize_text_field';
    }
  }
}
