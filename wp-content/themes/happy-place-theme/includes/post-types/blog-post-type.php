<?php
/**
 * Blog Post Type Registration
 * 
 * Registers a custom post type for blog posts to work with the universal system
 * 
 * @package Happy_Place_Theme
 */

if (!function_exists('hpt_register_blog_post_type')) {
    /**
     * Register the blog post type
     */
    function hpt_register_blog_post_type() {
        $labels = [
            'name'                  => _x('Blog Posts', 'Post type general name', 'happy-place-theme'),
            'singular_name'         => _x('Blog Post', 'Post type singular name', 'happy-place-theme'),
            'menu_name'             => _x('Blog', 'Admin Menu text', 'happy-place-theme'),
            'name_admin_bar'        => _x('Blog Post', 'Add New on Toolbar', 'happy-place-theme'),
            'add_new'               => __('Add New', 'happy-place-theme'),
            'add_new_item'          => __('Add New Blog Post', 'happy-place-theme'),
            'new_item'              => __('New Blog Post', 'happy-place-theme'),
            'edit_item'             => __('Edit Blog Post', 'happy-place-theme'),
            'view_item'             => __('View Blog Post', 'happy-place-theme'),
            'all_items'             => __('All Blog Posts', 'happy-place-theme'),
            'search_items'          => __('Search Blog Posts', 'happy-place-theme'),
            'parent_item_colon'     => __('Parent Blog Posts:', 'happy-place-theme'),
            'not_found'             => __('No blog posts found.', 'happy-place-theme'),
            'not_found_in_trash'    => __('No blog posts found in Trash.', 'happy-place-theme'),
            'featured_image'        => _x('Blog Post Featured Image', 'Overrides the "Featured Image" phrase', 'happy-place-theme'),
            'set_featured_image'    => _x('Set featured image', 'Overrides the "Set featured image" phrase', 'happy-place-theme'),
            'remove_featured_image' => _x('Remove featured image', 'Overrides the "Remove featured image" phrase', 'happy-place-theme'),
            'use_featured_image'    => _x('Use as featured image', 'Overrides the "Use as featured image" phrase', 'happy-place-theme'),
            'archives'              => _x('Blog Post archives', 'The post type archive label', 'happy-place-theme'),
            'insert_into_item'      => _x('Insert into blog post', 'Overrides the "Insert into post" phrase', 'happy-place-theme'),
            'uploaded_to_this_item' => _x('Uploaded to this blog post', 'Overrides the "Uploaded to this post" phrase', 'happy-place-theme'),
            'filter_items_list'     => _x('Filter blog posts list', 'Screen reader text for the filter links', 'happy-place-theme'),
            'items_list_navigation' => _x('Blog posts list navigation', 'Screen reader text for the pagination', 'happy-place-theme'),
            'items_list'            => _x('Blog posts list', 'Screen reader text for the items list', 'happy-place-theme'),
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
}

if (!function_exists('hpt_register_blog_taxonomies')) {
    /**
     * Register blog taxonomies
     */
    function hpt_register_blog_taxonomies() {
        // Blog Categories
        $category_labels = [
            'name'              => _x('Blog Categories', 'taxonomy general name', 'happy-place-theme'),
            'singular_name'     => _x('Blog Category', 'taxonomy singular name', 'happy-place-theme'),
            'search_items'      => __('Search Blog Categories', 'happy-place-theme'),
            'all_items'         => __('All Blog Categories', 'happy-place-theme'),
            'parent_item'       => __('Parent Blog Category', 'happy-place-theme'),
            'parent_item_colon' => __('Parent Blog Category:', 'happy-place-theme'),
            'edit_item'         => __('Edit Blog Category', 'happy-place-theme'),
            'update_item'       => __('Update Blog Category', 'happy-place-theme'),
            'add_new_item'      => __('Add New Blog Category', 'happy-place-theme'),
            'new_item_name'     => __('New Blog Category Name', 'happy-place-theme'),
            'menu_name'         => __('Categories', 'happy-place-theme'),
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
            'name'                       => _x('Blog Tags', 'taxonomy general name', 'happy-place-theme'),
            'singular_name'              => _x('Blog Tag', 'taxonomy singular name', 'happy-place-theme'),
            'search_items'               => __('Search Blog Tags', 'happy-place-theme'),
            'popular_items'              => __('Popular Blog Tags', 'happy-place-theme'),
            'all_items'                  => __('All Blog Tags', 'happy-place-theme'),
            'edit_item'                  => __('Edit Blog Tag', 'happy-place-theme'),
            'update_item'                => __('Update Blog Tag', 'happy-place-theme'),
            'add_new_item'               => __('Add New Blog Tag', 'happy-place-theme'),
            'new_item_name'              => __('New Blog Tag Name', 'happy-place-theme'),
            'separate_items_with_commas' => __('Separate blog tags with commas', 'happy-place-theme'),
            'add_or_remove_items'        => __('Add or remove blog tags', 'happy-place-theme'),
            'choose_from_most_used'      => __('Choose from the most used blog tags', 'happy-place-theme'),
            'not_found'                  => __('No blog tags found.', 'happy-place-theme'),
            'menu_name'                  => __('Tags', 'happy-place-theme'),
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
}

// Hook into WordPress
add_action('init', 'hpt_register_blog_post_type', 0);
add_action('init', 'hpt_register_blog_taxonomies', 0);

// Flush rewrite rules on theme activation
if (!function_exists('hpt_flush_rewrite_rules_on_activation')) {
    function hpt_flush_rewrite_rules_on_activation() {
        hpt_register_blog_post_type();
        hpt_register_blog_taxonomies();
        flush_rewrite_rules();
    }
}
add_action('after_switch_theme', 'hpt_flush_rewrite_rules_on_activation');

// Add to bridge adapter registration
if (!function_exists('hpt_register_blog_post_bridge')) {
    /**
     * Register blog post bridge adapter
     */
    function hpt_register_blog_post_bridge() {
        if (function_exists('hpt_register_card_data_adapter')) {
            hpt_register_card_data_adapter('blog_post', 'hpt_get_card_data_blog_post');
        }
    }
}
add_action('init', 'hpt_register_blog_post_bridge', 20);

/**
 * Get card data for blog posts
 * 
 * @param int $post_id Post ID
 * @return array Card data
 */
if (!function_exists('hpt_get_card_data_blog_post')) {
    function hpt_get_card_data_blog_post($post_id) {
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
}