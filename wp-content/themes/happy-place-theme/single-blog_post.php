<?php
/**
 * Single Blog Post Template - Magazine Style
 * 
 * @package Happy_Place_Theme
 */

// Get blog post data
$post_id = get_the_ID();
$title = get_the_title();
$content = get_the_content();
$excerpt = get_the_excerpt();
$featured_image_id = get_post_thumbnail_id();

// Get categories and tags
$categories = get_the_terms($post_id, 'blog_category');
$tags = get_the_terms($post_id, 'blog_tag');

// Get author info
$author_id = get_the_author_meta('ID');
$author_name = get_the_author_meta('display_name');
$author_bio = get_the_author_meta('description');
$author_avatar = get_avatar_url($author_id, ['size' => 80]);

// Calculate read time
$word_count = str_word_count(strip_tags($content));
$read_time = ceil($word_count / 200);

// Get primary category for badge
$primary_category = '';
if ($categories && !is_wp_error($categories)) {
    $primary_category = $categories[0]->name;
}

// Format date
$post_date = get_the_date('F j, Y');
$post_time = get_the_date('c');

// Check if featured post
$is_featured = get_post_meta($post_id, 'featured_post', true) === '1';

// Get related posts for later section
$related_posts = get_posts([
    'post_type' => 'blog_post',
    'posts_per_page' => 3,
    'post__not_in' => [$post_id],
    'tax_query' => [
        [
            'taxonomy' => 'blog_category',
            'field' => 'term_id',
            'terms' => wp_get_post_terms($post_id, 'blog_category', ['fields' => 'ids'])
        ]
    ]
]);

// Build social share buttons
$share_buttons = [
    [
        'text' => 'Share on Twitter',
        'url' => 'https://twitter.com/intent/tweet?url=' . urlencode(get_permalink()) . '&text=' . urlencode($title),
        'style' => 'outline-primary',
        'icon' => 'twitter',
        'target' => '_blank'
    ],
    [
        'text' => 'Share on Facebook',
        'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode(get_permalink()),
        'style' => 'outline-primary',
        'icon' => 'facebook',
        'target' => '_blank'
    ],
    [
        'text' => 'Share on LinkedIn',
        'url' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode(get_permalink()),
        'style' => 'outline-primary',
        'icon' => 'linkedin',
        'target' => '_blank'
    ]
];

get_header();
?>

<main class="hph-main">

    <?php
    // Hero Section - Featured image with article metadata
    get_template_part('template-parts/sections/hero', null, [
        'style' => 'image',
        'height' => 'lg',
        'is_top_of_page' => true,
        'background_image' => $featured_image_id ? wp_get_attachment_image_url($featured_image_id, 'full') : '',
        'overlay' => 'dark',
        'overlay_opacity' => '50',
        'alignment' => 'center',
        'content_width' => 'normal',
        'badge' => $is_featured ? 'Featured Article' : $primary_category,
        'headline' => $title,
        'subheadline' => $excerpt ?: wp_trim_words($content, 25),
        'meta' => [
            'author' => $author_name,
            'date' => $post_date,
            'read_time' => $read_time . ' min read'
        ],
        'buttons' => [
            [
                'text' => 'Read Article',
                'url' => '#article-content',
                'style' => 'primary',
                'icon' => 'arrow-down',
                'scroll_to' => true
            ]
        ],
        'section_id' => 'article-hero'
    ]);
    ?>

    <?php if ($author_bio): ?>
    <?php
    // Author Introduction Section
    get_template_part('template-parts/sections/author-bio', null, [
        'background' => 'light',
        'padding' => 'lg',
        'author_id' => $author_id,
        'author_name' => $author_name,
        'author_bio' => $author_bio,
        'author_avatar' => $author_avatar,
        'post_count' => count_user_posts($author_id, 'blog_post'),
        'section_id' => 'author-intro'
    ]);
    ?>
    <?php endif; ?>

    <?php
    // Main Article Content
    get_template_part('template-parts/sections/content', null, [
        'layout' => 'centered',
        'background' => 'white',
        'padding' => 'xl',
        'content_width' => 'narrow',
        'alignment' => 'left',
        'content' => $content,
        'section_id' => 'article-content'
    ]);
    ?>

    <?php if ($tags && !is_wp_error($tags)): ?>
    <?php
    // Tags Section
    $tag_links = [];
    foreach ($tags as $tag) {
        $tag_links[] = [
            'text' => '#' . $tag->name,
            'url' => get_term_link($tag),
            'style' => 'tag'
        ];
    }
    
    get_template_part('template-parts/sections/content', null, [
        'layout' => 'centered',
        'background' => 'light',
        'padding' => 'md',
        'content_width' => 'narrow',
        'alignment' => 'center',
        'badge' => 'Tagged:',
        'buttons' => $tag_links,
        'section_id' => 'article-tags'
    ]);
    ?>
    <?php endif; ?>

    <?php
    // Social Share Section
    get_template_part('template-parts/sections/content', null, [
        'layout' => 'centered',
        'background' => 'white',
        'padding' => 'lg',
        'content_width' => 'normal',
        'alignment' => 'center',
        'headline' => 'Share this article',
        'subheadline' => 'Help others discover this content',
        'buttons' => $share_buttons,
        'section_id' => 'article-share'
    ]);
    ?>

    <?php if ($author_bio): ?>
    <?php
    // Detailed Author Section
    get_template_part('template-parts/sections/author-profile', null, [
        'background' => 'light',
        'padding' => 'xl',
        'author_id' => $author_id,
        'author_name' => $author_name,
        'author_bio' => $author_bio,
        'author_avatar' => $author_avatar,
        'author_posts_url' => get_author_posts_url($author_id),
        'recent_posts_count' => 3,
        'section_id' => 'author-profile'
    ]);
    ?>
    <?php endif; ?>

    <?php if ($related_posts): ?>
    <?php
    // Related Articles Section
    $related_features = [];
    foreach ($related_posts as $related_post) {
        $related_image = get_the_post_thumbnail_url($related_post->ID, 'medium');
        $related_category = '';
        $related_cats = get_the_terms($related_post->ID, 'blog_category');
        if ($related_cats && !is_wp_error($related_cats)) {
            $related_category = $related_cats[0]->name;
        }
        
        $related_features[] = [
            'image' => $related_image,
            'badge' => $related_category,
            'title' => $related_post->post_title,
            'content' => wp_trim_words($related_post->post_content, 20),
            'link' => get_permalink($related_post->ID),
            'date' => get_the_date('M j, Y', $related_post->ID)
        ];
    }
    
    get_template_part('template-parts/sections/features', null, [
        'layout' => 'cards',
        'background' => 'white',
        'padding' => 'xl',
        'columns' => min(3, count($related_features)),
        'badge' => 'Related Articles',
        'headline' => 'Continue Reading',
        'subheadline' => 'More articles you might enjoy',
        'features' => $related_features,
        'card_style' => 'article',
        'section_id' => 'related-articles'
    ]);
    ?>
    <?php endif; ?>

    <?php
    // Newsletter Signup Section
    get_template_part('template-parts/sections/newsletter-signup', null, [
        'background' => 'primary',
        'padding' => 'xl',
        'alignment' => 'center',
        'content_width' => 'normal',
        'headline' => 'Never Miss an Update',
        'subheadline' => 'Subscribe to our newsletter for the latest articles and insights',
        'section_id' => 'newsletter-signup'
    ]);
    ?>

    <?php if (comments_open() || get_comments_number()): ?>
    <?php
    // Comments Section
    get_template_part('template-parts/sections/comments', null, [
        'background' => 'light',
        'padding' => 'xl',
        'content_width' => 'narrow',
        'section_id' => 'article-comments'
    ]);
    ?>
    <?php endif; ?>

</main>

<?php
get_footer();