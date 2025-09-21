<?php
/**
 * Base Breadcrumbs - Site navigation breadcrumbs
 *
 * @package HappyPlaceTheme
 */

// Parse arguments with utility-first approach
$breadcrumb_args = wp_parse_args($args ?? [], [
    'post_id' => get_queried_object_id(),
    'post_type' => get_post_type(),
    'separator' => '<i class="fas fa-chevron-right hph-text-gray-400 hph-text-sm"></i>',
    'home_text' => __('Home', 'happy-place-theme'),
    'show_home' => true,
    'show_current' => true,
    'container_classes' => [
        'hph-breadcrumbs',
        'hph-py-md',
        'hph-text-sm'
    ],
    'list_classes' => [
        'hph-flex',
        'hph-items-center',
        'hph-space-x-sm',
        'hph-flex-wrap'
    ],
    'item_classes' => [
        'hph-flex',
        'hph-items-center',
        'hph-space-x-sm'
    ],
    'link_classes' => [
        'hph-text-gray-600',
        'hph-hover:text-primary-600',
        'hph-transition-colors',
        'hph-duration-200',
        'hph-font-medium'
    ],
    'current_classes' => [
        'hph-text-gray-900',
        'hph-font-medium',
        'hph-truncate'
    ],
    'separator_classes' => [
        'hph-flex-shrink-0',
        'hph-text-gray-400'
    ]
]);

// Build breadcrumb items
$breadcrumbs = [];

// Home
if ($breadcrumb_args['show_home']) {
    $breadcrumbs[] = [
        'text' => $breadcrumb_args['home_text'],
        'url' => home_url('/'),
        'is_current' => false
    ];
}

// Check what type of page we're on
if (is_front_page()) {
    // Don't show breadcrumbs on front page
    return;
} elseif (is_home()) {
    // Blog home
    $breadcrumbs[] = [
        'text' => __('Blog', 'happy-place-theme'),
        'url' => get_permalink(get_option('page_for_posts')),
        'is_current' => true
    ];
} elseif (is_search()) {
    // Search results
    $breadcrumbs[] = [
        'text' => sprintf(__('Search Results for "%s"', 'happy-place-theme'), get_search_query()),
        'url' => '',
        'is_current' => true
    ];
} elseif (is_404()) {
    // 404 page
    $breadcrumbs[] = [
        'text' => __('Page Not Found', 'happy-place-theme'),
        'url' => '',
        'is_current' => true
    ];
} elseif (is_category()) {
    // Category archive
    $category = get_queried_object();
    
    // Add parent categories
    if ($category->parent) {
        $parents = get_category_parents($category->parent, true, '|||');
        $parents = explode('|||', $parents);
        foreach ($parents as $parent) {
            if (!empty(trim($parent))) {
                $breadcrumbs[] = [
                    'text' => strip_tags($parent),
                    'url' => '',
                    'is_current' => false
                ];
            }
        }
    }
    
    $breadcrumbs[] = [
        'text' => $category->name,
        'url' => get_category_link($category->term_id),
        'is_current' => true
    ];
} elseif (is_tag()) {
    // Tag archive
    $tag = get_queried_object();
    $breadcrumbs[] = [
        'text' => sprintf(__('Tagged "%s"', 'happy-place-theme'), $tag->name),
        'url' => get_tag_link($tag->term_id),
        'is_current' => true
    ];
} elseif (is_tax()) {
    // Custom taxonomy
    $term = get_queried_object();
    $taxonomy = get_taxonomy($term->taxonomy);
    
    // Add post type archive if it exists
    if ($taxonomy->object_type && !empty($taxonomy->object_type[0])) {
        $post_type = $taxonomy->object_type[0];
        $post_type_object = get_post_type_object($post_type);
        $archive_link = get_post_type_archive_link($post_type);
        
        if ($archive_link && $post_type_object) {
            $breadcrumbs[] = [
                'text' => $post_type_object->labels->name,
                'url' => $archive_link,
                'is_current' => false
            ];
        }
    }
    
    // Add parent terms
    if ($term->parent) {
        $parents = get_ancestors($term->term_id, $term->taxonomy);
        $parents = array_reverse($parents);
        foreach ($parents as $parent_id) {
            $parent = get_term($parent_id, $term->taxonomy);
            $breadcrumbs[] = [
                'text' => $parent->name,
                'url' => get_term_link($parent),
                'is_current' => false
            ];
        }
    }
    
    $breadcrumbs[] = [
        'text' => $term->name,
        'url' => get_term_link($term),
        'is_current' => true
    ];
} elseif (is_post_type_archive()) {
    // Post type archive
    $post_type = get_queried_object();
    $breadcrumbs[] = [
        'text' => $post_type->labels->name,
        'url' => get_post_type_archive_link($post_type->name),
        'is_current' => true
    ];
} elseif (is_singular()) {
    // Single post
    $post = get_queried_object();
    $post_type_object = get_post_type_object($post->post_type);
    
    // Add post type archive if not a regular post
    if ($post->post_type !== 'post' && $post_type_object->has_archive) {
        $archive_link = get_post_type_archive_link($post->post_type);
        if ($archive_link) {
            $breadcrumbs[] = [
                'text' => $post_type_object->labels->name,
                'url' => $archive_link,
                'is_current' => false
            ];
        }
    }
    
    // Add taxonomies for context
    if ($post->post_type === 'post') {
        // For regular posts, add primary category
        $categories = get_the_category($post->ID);
        if (!empty($categories)) {
            $primary_category = $categories[0];
            $breadcrumbs[] = [
                'text' => $primary_category->name,
                'url' => get_category_link($primary_category->term_id),
                'is_current' => false
            ];
        }
    } else {
        // For custom post types, add relevant taxonomies
        $taxonomies = get_object_taxonomies($post->post_type, 'objects');
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy->public && !$taxonomy->_builtin) {
                $terms = get_the_terms($post->ID, $taxonomy->name);
                if (!empty($terms) && !is_wp_error($terms)) {
                    $primary_term = $terms[0];
                    $breadcrumbs[] = [
                        'text' => $primary_term->name,
                        'url' => get_term_link($primary_term),
                        'is_current' => false
                    ];
                    break; // Only show one taxonomy
                }
            }
        }
    }
    
    // Current post
    $breadcrumbs[] = [
        'text' => get_the_title($post->ID),
        'url' => get_permalink($post->ID),
        'is_current' => true
    ];
} elseif (is_page()) {
    // Page
    $page = get_queried_object();
    
    // Add parent pages
    if ($page->post_parent) {
        $parents = get_post_ancestors($page->ID);
        $parents = array_reverse($parents);
        foreach ($parents as $parent_id) {
            $breadcrumbs[] = [
                'text' => get_the_title($parent_id),
                'url' => get_permalink($parent_id),
                'is_current' => false
            ];
        }
    }
    
    $breadcrumbs[] = [
        'text' => get_the_title($page->ID),
        'url' => get_permalink($page->ID),
        'is_current' => true
    ];
}

// Don't show if only home breadcrumb
if (count($breadcrumbs) <= 1) {
    return;
}

// Filter out current item if not showing it
if (!$breadcrumb_args['show_current']) {
    $breadcrumbs = array_filter($breadcrumbs, function($item) {
        return !$item['is_current'];
    });
}

// Don't show if no breadcrumbs after filtering
if (empty($breadcrumbs)) {
    return;
}
?>

<nav class="<?php echo esc_attr(implode(' ', $breadcrumb_args['container_classes'])); ?>" aria-label="<?php esc_attr_e('Breadcrumb', 'happy-place-theme'); ?>">
    <ol class="<?php echo esc_attr(implode(' ', $breadcrumb_args['list_classes'])); ?>">
        
        <?php foreach ($breadcrumbs as $index => $breadcrumb) : ?>
            
            <li class="<?php echo esc_attr(implode(' ', $breadcrumb_args['item_classes'])); ?>">
                
                <?php if (!$breadcrumb['is_current'] && !empty($breadcrumb['url'])) : ?>
                    <a href="<?php echo esc_url($breadcrumb['url']); ?>" 
                       class="<?php echo esc_attr(implode(' ', $breadcrumb_args['link_classes'])); ?>">
                        <?php echo esc_html($breadcrumb['text']); ?>
                    </a>
                <?php else : ?>
                    <span class="<?php echo esc_attr(implode(' ', $breadcrumb_args['current_classes'])); ?>" aria-current="page">
                        <?php echo esc_html($breadcrumb['text']); ?>
                    </span>
                <?php endif; ?>
                
                <?php if ($index < count($breadcrumbs) - 1) : ?>
                    <span class="<?php echo esc_attr(implode(' ', $breadcrumb_args['separator_classes'])); ?>" aria-hidden="true">
                        <?php echo $breadcrumb_args['separator']; ?>
                    </span>
                <?php endif; ?>
                
            </li>
            
        <?php endforeach; ?>
        
    </ol>
</nav>
