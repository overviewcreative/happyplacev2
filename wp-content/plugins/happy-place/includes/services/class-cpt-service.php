<?php
/**
 * Custom Post Type Service
 *
 * Handles all custom post type and taxonomy registration
 * Migrated from theme to establish proper plugin-theme separation
 *
 * @package HappyPlace
 * @subpackage Services
 * @since 4.3.0 - Migrated from theme
 */

namespace HappyPlace\Services;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Custom Post Type Service Class
 *
 * Manages all custom post types and taxonomies for Happy Place
 */
class CPTService {

    /**
     * Service initialization
     */
    public function init() {
        // Register post types and taxonomies
        add_action('init', [$this, 'register_blog_post_type'], 0);
        add_action('init', [$this, 'register_blog_taxonomies'], 0);

        // Flush rewrite rules when needed
        add_action('after_switch_theme', [$this, 'flush_rewrite_rules_on_activation']);

        // Register bridge adapter for blog posts
        add_action('init', [$this, 'register_blog_post_bridge'], 20);
    }

    /**
     * Register the blog post type
     */
    public function register_blog_post_type() {
        $labels = [
            'name'                  => _x('Blog Posts', 'Post type general name', 'happy-place'),
            'singular_name'         => _x('Blog Post', 'Post type singular name', 'happy-place'),
            'menu_name'             => _x('Blog', 'Admin Menu text', 'happy-place'),
            'name_admin_bar'        => _x('Blog Post', 'Add New on Toolbar', 'happy-place'),
            'add_new'               => __('Add New', 'happy-place'),
            'add_new_item'          => __('Add New Blog Post', 'happy-place'),
            'new_item'              => __('New Blog Post', 'happy-place'),
            'edit_item'             => __('Edit Blog Post', 'happy-place'),
            'view_item'             => __('View Blog Post', 'happy-place'),
            'all_items'             => __('All Blog Posts', 'happy-place'),
            'search_items'          => __('Search Blog Posts', 'happy-place'),
            'parent_item_colon'     => __('Parent Blog Posts:', 'happy-place'),
            'not_found'             => __('No blog posts found.', 'happy-place'),
            'not_found_in_trash'    => __('No blog posts found in Trash.', 'happy-place'),
            'featured_image'        => _x('Blog Post Featured Image', 'Overrides the "Featured Image" phrase', 'happy-place'),
            'set_featured_image'    => _x('Set featured image', 'Overrides the "Set featured image" phrase', 'happy-place'),
            'remove_featured_image' => _x('Remove featured image', 'Overrides the "Remove featured image" phrase', 'happy-place'),
            'use_featured_image'    => _x('Use as featured image', 'Overrides the "Use as featured image" phrase', 'happy-place'),
            'archives'              => _x('Blog Post archives', 'The post type archive label', 'happy-place'),
            'insert_into_item'      => _x('Insert into blog post', 'Overrides the "Insert into post" phrase', 'happy-place'),
            'uploaded_to_this_item' => _x('Uploaded to this blog post', 'Overrides the "Uploaded to this post" phrase', 'happy-place'),
            'filter_items_list'     => _x('Filter blog posts list', 'Screen reader text for the filter links', 'happy-place'),
            'items_list_navigation' => _x('Blog posts list navigation', 'Screen reader text for the pagination', 'happy-place'),
            'items_list'            => _x('Blog posts list', 'Screen reader text for the items list', 'happy-place'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => [
                'slug' => 'blog',
                'with_front' => false
            ],
            'capability_type'    => 'post',
            'has_archive'        => 'blog',
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-admin-post',
            'show_in_rest'       => true,
            'supports'           => [
                'title',
                'editor',
                'author',
                'thumbnail',
                'excerpt',
                'comments',
                'trackbacks',
                'custom-fields',
                'revisions',
                'page-attributes',
                'post-formats'
            ],
            'taxonomies'         => ['blog_category', 'blog_tag'],
            'show_in_admin_bar'  => true,
            'can_export'         => true,
            'exclude_from_search' => false,
        ];

        register_post_type('blog_post', $args);
    }

    /**
     * Register blog taxonomies
     */
    public function register_blog_taxonomies() {
        // Blog Categories
        $category_labels = [
            'name'              => _x('Blog Categories', 'taxonomy general name', 'happy-place'),
            'singular_name'     => _x('Blog Category', 'taxonomy singular name', 'happy-place'),
            'search_items'      => __('Search Blog Categories', 'happy-place'),
            'all_items'         => __('All Blog Categories', 'happy-place'),
            'parent_item'       => __('Parent Blog Category', 'happy-place'),
            'parent_item_colon' => __('Parent Blog Category:', 'happy-place'),
            'edit_item'         => __('Edit Blog Category', 'happy-place'),
            'update_item'       => __('Update Blog Category', 'happy-place'),
            'add_new_item'      => __('Add New Blog Category', 'happy-place'),
            'new_item_name'     => __('New Blog Category Name', 'happy-place'),
            'menu_name'         => __('Categories', 'happy-place'),
        ];

        register_taxonomy('blog_category', ['blog_post'], [
            'hierarchical'      => true,
            'labels'            => $category_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => [
                'slug' => 'blog/category',
                'with_front' => false
            ],
        ]);

        // Blog Tags
        $tag_labels = [
            'name'                       => _x('Blog Tags', 'taxonomy general name', 'happy-place'),
            'singular_name'              => _x('Blog Tag', 'taxonomy singular name', 'happy-place'),
            'search_items'               => __('Search Blog Tags', 'happy-place'),
            'popular_items'              => __('Popular Blog Tags', 'happy-place'),
            'all_items'                  => __('All Blog Tags', 'happy-place'),
            'edit_item'                  => __('Edit Blog Tag', 'happy-place'),
            'update_item'                => __('Update Blog Tag', 'happy-place'),
            'add_new_item'               => __('Add New Blog Tag', 'happy-place'),
            'new_item_name'              => __('New Blog Tag Name', 'happy-place'),
            'separate_items_with_commas' => __('Separate blog tags with commas', 'happy-place'),
            'add_or_remove_items'        => __('Add or remove blog tags', 'happy-place'),
            'choose_from_most_used'      => __('Choose from the most used blog tags', 'happy-place'),
            'not_found'                  => __('No blog tags found.', 'happy-place'),
            'menu_name'                  => __('Tags', 'happy-place'),
        ];

        register_taxonomy('blog_tag', ['blog_post'], [
            'hierarchical'          => false,
            'labels'                => $tag_labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
            'show_in_rest'          => true,
            'rewrite'               => [
                'slug' => 'blog/tag',
                'with_front' => false
            ],
        ]);
    }

    /**
     * Flush rewrite rules on theme activation
     */
    public function flush_rewrite_rules_on_activation() {
        $this->register_blog_post_type();
        $this->register_blog_taxonomies();
        flush_rewrite_rules();
    }

    /**
     * Register blog post bridge adapter
     */
    public function register_blog_post_bridge() {
        // Only register if bridge function exists (theme is active)
        if (function_exists('hpt_register_card_data_adapter')) {
            hpt_register_card_data_adapter('blog_post', [$this, 'get_blog_post_card_data']);
        }
    }

    /**
     * Get card data for blog posts
     *
     * @param int $post_id Post ID
     * @return array Card data
     */
    public function get_blog_post_card_data($post_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'blog_post') {
            return [];
        }

        // Get featured image
        $image_id = get_post_thumbnail_id($post_id);
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium_large') : '';
        $image_alt = $image_id ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';

        // Get categories
        $categories = get_the_terms($post_id, 'blog_category');
        $category_names = [];
        $category_links = [];

        if ($categories && !is_wp_error($categories)) {
            foreach ($categories as $category) {
                $category_names[] = $category->name;
                $category_links[] = [
                    'name' => $category->name,
                    'url' => get_term_link($category)
                ];
            }
        }

        // Get tags
        $tags = get_the_terms($post_id, 'blog_tag');
        $tag_names = [];

        if ($tags && !is_wp_error($tags)) {
            foreach ($tags as $tag) {
                $tag_names[] = $tag->name;
            }
        }

        // Get author info
        $author_id = $post->post_author;
        $author_name = get_the_author_meta('display_name', $author_id);
        $author_url = get_author_posts_url($author_id);

        // Get excerpt
        $excerpt = has_excerpt($post_id) ? get_the_excerpt($post_id) : wp_trim_words($post->post_content, 25);

        // Read time calculation
        $word_count = str_word_count(strip_tags($post->post_content));
        $read_time = ceil($word_count / 200); // Average reading speed

        return [
            'id' => $post_id,
            'title' => get_the_title($post_id),
            'excerpt' => $excerpt,
            'permalink' => get_permalink($post_id),
            'featured_image' => [
                'url' => $image_url,
                'alt' => $image_alt ?: $post->post_title
            ],
            'date' => [
                'published' => get_the_date('c', $post_id),
                'formatted' => get_the_date('M j, Y', $post_id),
                'relative' => human_time_diff(get_the_time('U', $post_id), current_time('timestamp')) . ' ago'
            ],
            'author' => [
                'name' => $author_name,
                'url' => $author_url,
                'avatar' => get_avatar_url($author_id, ['size' => 64])
            ],
            'categories' => $category_names,
            'category_links' => $category_links,
            'tags' => $tag_names,
            'read_time' => $read_time,
            'comment_count' => get_comments_number($post_id),
            'post_type' => 'blog_post',
            'status' => $post->post_status,
            'type_label' => 'Blog Post',
            'meta' => [
                'featured' => get_post_meta($post_id, 'featured_post', true) === '1',
                'word_count' => $word_count
            ]
        ];
    }

    /**
     * Get all registered custom post types managed by this service
     */
    public function get_managed_post_types() {
        return ['blog_post'];
    }

    /**
     * Get all registered taxonomies managed by this service
     */
    public function get_managed_taxonomies() {
        return ['blog_category', 'blog_tag'];
    }

    /**
     * Check if a post type is managed by this service
     */
    public function is_managed_post_type($post_type) {
        return in_array($post_type, $this->get_managed_post_types());
    }
}