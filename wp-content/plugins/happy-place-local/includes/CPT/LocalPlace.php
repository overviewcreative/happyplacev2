<?php
namespace HappyPlace\Local\CPT;

class LocalPlace {
  const TYPE = 'local_place';
  
  public static function register() {
    register_post_type(self::TYPE, [
      'labels' => [
        'name' => __('Local Places', 'happy-place-local'),
        'singular_name' => __('Local Place', 'happy-place-local'),
        'add_new' => __('Add New', 'happy-place-local'),
        'add_new_item' => __('Add New Local Place', 'happy-place-local'),
        'edit_item' => __('Edit Local Place', 'happy-place-local'),
        'new_item' => __('New Local Place', 'happy-place-local'),
        'view_item' => __('View Local Place', 'happy-place-local'),
        'search_items' => __('Search Local Places', 'happy-place-local'),
        'not_found' => __('No local places found', 'happy-place-local'),
        'not_found_in_trash' => __('No local places found in trash', 'happy-place-local'),
      ],
      'description' => __('Local places and businesses', 'happy-place-local'),
      'public' => true,
      'publicly_queryable' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'query_var' => true,
      'rewrite' => ['slug' => 'places', 'with_front' => false],
      'capability_type' => 'post',
      'has_archive' => true,
      'hierarchical' => false,
      'menu_position' => 25,
      'menu_icon' => 'dashicons-location-alt',
      'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'],
      'show_in_rest' => true,
      'rest_base' => 'local_places',
    ]);

    // Register meta fields for REST API
    self::register_meta_fields();
  }

  private static function register_meta_fields() {
    $meta_fields = [
      'hpl_city_slug' => 'string',
      'hpl_city_id' => 'integer',
      'address' => 'string',
      'website' => 'string', 
      'phone' => 'string',
      'price_range' => 'string',
      'is_family_friendly' => 'boolean',
      'lat' => 'number',
      'lng' => 'number',
      'hours_json' => 'string',
      'source_url' => 'string',
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
