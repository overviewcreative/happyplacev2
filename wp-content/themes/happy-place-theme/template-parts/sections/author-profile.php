<?php
/**
 * Author Profile Section Template Part
 * 
 * @package Happy_Place_Theme
 */

$author_id = $args['author_id'] ?? 0; // Author ID for fetching data
$author_name = $args['author_name'] ?? ''; // Author display name
$author_bio = $args['author_bio'] ?? ''; // Author biography text
$author_avatar = $args['author_avatar'] ?? ''; // URL to author avatar image
$author_posts_url = $args['author_posts_url'] ?? ''; // URL to view all posts by author
$recent_posts_count = $args['recent_posts_count'] ?? 3; // Number of recent posts to display
$background = $args['background'] ?? 'light'; // Options: 'light', 'dark', 'white', 'gray', 'primary'
$padding = $args['padding'] ?? 'xl'; // Options: 'sm', 'md', 'lg', 'xl', 'none'
$section_id = $args['section_id'] ?? ''; // HTML ID for the section

if (!$author_id) {
    return;
}

// Get recent posts by this author
$recent_posts = get_posts([
    'author' => $author_id,
    'post_type' => 'blog_post',
    'posts_per_page' => $recent_posts_count,
    'post_status' => 'publish'
]);

$section_classes = [
    'hph-section',
    'hph-author-profile-section',
    'hph-bg-' . $background,
    'hph-py-' . $padding
];
?>

<section <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?> 
         class="<?php echo esc_attr(implode(' ', $section_classes)); ?>">
    <div class="hph-container">
        <div class="hph-content-width-wide hph-mx-auto">
            
            <div class="hph-author-profile hph-grid hph-lg:grid-cols-3 hph-gap-2xl">
                
                <!-- Author Info Column -->
                <div class="hph-author-info hph-lg:col-span-1">
                    <div class="hph-author-card hph-bg-white hph-p-xl hph-rounded-lg hph-shadow-sm hph-text-center">
                        
                        <?php if ($author_avatar): ?>
                            <div class="hph-author-avatar hph-mb-lg">
                                <img src="<?php echo esc_url($author_avatar); ?>" 
                                     alt="<?php echo esc_attr($author_name); ?>"
                                     class="hph-w-24 hph-h-24 hph-rounded-full hph-mx-auto hph-border-4 hph-border-primary">
                            </div>
                        <?php endif; ?>
                        
                        <h3 class="hph-text-2xl hph-font-bold hph-mb-sm hph-text-gray-900">
                            <?php echo esc_html($author_name); ?>
                        </h3>
                        
                        <p class="hph-text-primary hph-font-medium hph-mb-lg">
                            Contributing Author
                        </p>
                        
                        <?php if ($author_bio): ?>
                            <p class="hph-text-gray-600 hph-leading-relaxed hph-mb-lg">
                                <?php echo esc_html($author_bio); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($author_posts_url): ?>
                            <a href="<?php echo esc_url($author_posts_url); ?>" 
                               class="hph-btn hph-btn-primary hph-btn-sm">
                                <i class="fas fa-user"></i>
                                View All Articles
                            </a>
                        <?php endif; ?>
                        
                    </div>
                </div>
                
                <!-- Recent Posts Column -->
                <div class="hph-recent-posts hph-lg:col-span-2">
                    <h4 class="hph-text-xl hph-font-bold hph-mb-lg hph-text-gray-900">
                        More from <?php echo esc_html($author_name); ?>
                    </h4>
                    
                    <?php if ($recent_posts): ?>
                        <div class="hph-recent-posts-grid hph-grid hph-gap-lg">
                            <?php foreach ($recent_posts as $post): 
                                setup_postdata($post);
                                $post_categories = get_the_terms($post->ID, 'blog_category');
                                $primary_category = '';
                                if ($post_categories && !is_wp_error($post_categories)) {
                                    $primary_category = $post_categories[0]->name;
                                }
                            ?>
                                <article class="hph-recent-post hph-flex hph-gap-lg hph-p-lg hph-bg-white hph-rounded-lg hph-shadow-sm hover:hph-shadow-md hph-transition">
                                    
                                    <?php if (has_post_thumbnail($post->ID)): ?>
                                        <div class="hph-post-thumbnail hph-flex-shrink-0">
                                            <a href="<?php echo get_permalink($post->ID); ?>">
                                                <?php echo get_the_post_thumbnail($post->ID, 'thumbnail', [
                                                    'class' => 'hph-w-20 hph-h-20 hph-object-cover hph-rounded'
                                                ]); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="hph-post-content">
                                        <?php if ($primary_category): ?>
                                            <span class="hph-post-category hph-text-xs hph-text-primary hph-font-medium hph-uppercase hph-tracking-wide">
                                                <?php echo esc_html($primary_category); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <h5 class="hph-post-title hph-text-lg hph-font-bold hph-mb-xs hph-leading-tight">
                                            <a href="<?php echo get_permalink($post->ID); ?>" 
                                               class="hph-text-gray-900 hover:hph-text-primary hph-transition">
                                                <?php echo get_the_title($post->ID); ?>
                                            </a>
                                        </h5>
                                        
                                        <p class="hph-post-excerpt hph-text-sm hph-text-gray-600 hph-mb-sm">
                                            <?php echo wp_trim_words(get_the_excerpt($post->ID), 15); ?>
                                        </p>
                                        
                                        <time class="hph-post-date hph-text-xs hph-text-gray-500">
                                            <?php echo get_the_date('M j, Y', $post->ID); ?>
                                        </time>
                                    </div>
                                    
                                </article>
                            <?php endforeach; ?>
                            <?php wp_reset_postdata(); ?>
                        </div>
                    <?php else: ?>
                        <p class="hph-text-gray-600">No other articles available from this author.</p>
                    <?php endif; ?>
                </div>
                
            </div>
            
        </div>
    </div>
</section>