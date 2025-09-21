<?php
/**
 * Blog Home Template
 * 
 * Main blog page template using the universal hero system
 * 
 * @package Happy_Place_Theme
 */

get_header();

// Get hero data for blog posts
$hero_data = hpt_get_archive_hero_data('post', [
    'title' => 'Latest News & Insights',
    'subtitle' => 'Stay informed with our latest articles, market updates, and expert insights from our team'
]);
?>

<main class="hph-main-content">
    
    <?php 
    // Universal hero section
    hpt_render_archive_hero($hero_data); 
    ?>
    
    <div class="hph-blog-home-container hph-container hph-py-3xl">
        
        <?php if (have_posts()): ?>
            
            <!-- Featured Posts Section -->
            <?php 
            $featured_posts = get_posts([
                'post_type' => 'post',
                'posts_per_page' => 3,
                'meta_query' => [
                    [
                        'key' => 'featured_post',
                        'value' => '1',
                        'compare' => '='
                    ]
                ]
            ]);
            
            if (!empty($featured_posts)):
            ?>
                <section class="hph-featured-posts hph-mb-3xl">
                    <div class="hph-section-header hph-text-center hph-mb-xl">
                        <h2 class="hph-text-3xl hph-font-bold hph-mb-md">Featured Articles</h2>
                        <p class="hph-text-gray-600">Don't miss these important updates and insights</p>
                    </div>
                    
                    <div class="hph-featured-grid hph-grid hph-lg:grid-cols-3 hph-gap-xl">
                        <?php foreach ($featured_posts as $post): 
                            setup_postdata($post);
                        ?>
                            <article class="hph-featured-card hph-bg-white hph-rounded-lg hph-shadow-md hph-overflow-hidden hover:hph-shadow-lg hph-transition">
                                
                                <?php if (has_post_thumbnail()): ?>
                                    <div class="hph-featured-image hph-h-48 hph-overflow-hidden">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('medium_large', [
                                                'class' => 'hph-w-full hph-h-full hph-object-cover hover:hph-scale-105 hph-transition'
                                            ]); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="hph-featured-content hph-p-lg">
                                    <div class="hph-featured-meta hph-mb-sm">
                                        <span class="hph-badge hph-badge-primary">Featured</span>
                                        <time class="hph-text-sm hph-text-gray-500 hph-ml-sm">
                                            <?php echo get_the_date('M j, Y'); ?>
                                        </time>
                                    </div>
                                    
                                    <h3 class="hph-featured-title hph-text-xl hph-font-bold hph-mb-sm">
                                        <a href="<?php the_permalink(); ?>" class="hph-text-gray-900 hover:hph-text-primary hph-transition">
                                            <?php the_title(); ?>
                                        </a>
                                    </h3>
                                    
                                    <p class="hph-featured-excerpt hph-text-gray-600 hph-mb-md">
                                        <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                                    </p>
                                    
                                    <a href="<?php the_permalink(); ?>" class="hph-read-more hph-text-primary hph-font-medium hover:hph-text-primary-dark hph-transition">
                                        Read More <i class="fas fa-arrow-right hph-ml-xs"></i>
                                    </a>
                                </div>
                                
                            </article>
                        <?php endforeach; ?>
                        <?php wp_reset_postdata(); ?>
                    </div>
                </section>
            <?php endif; ?>
            
            <!-- Recent Posts Section -->
            <section class="hph-recent-posts">
                <div class="hph-section-header hph-text-center hph-mb-xl">
                    <h2 class="hph-text-3xl hph-font-bold hph-mb-md">Recent Articles</h2>
                    <p class="hph-text-gray-600">Stay up to date with our latest content</p>
                </div>
                
                <div class="hph-blog-grid hph-grid hph-grid-auto-fit-md hph-gap-lg">
                    <?php while (have_posts()): the_post(); ?>
                        <?php 
                        // Use universal card system
                        get_template_part('template-parts/cards/universal-card', null, [
                            'post_type' => 'post',
                            'post_id' => get_the_ID()
                        ]); 
                        ?>
                    <?php endwhile; ?>
                </div>
                
                <?php 
                // Pagination
                ?>
                <div class="hph-pagination-container hph-mt-3xl">
                    <?php
                    the_posts_pagination([
                        'mid_size' => 2,
                        'prev_text' => '<i class="fas fa-chevron-left"></i> Previous',
                        'next_text' => 'Next <i class="fas fa-chevron-right"></i>',
                        'screen_reader_text' => 'Blog navigation'
                    ]);
                    ?>
                </div>
            </section>
            
        <?php else: ?>
            
            <div class="hph-empty-state hph-text-center hph-py-3xl">
                <i class="fas fa-newspaper hph-text-6xl hph-text-gray-300 hph-mb-lg"></i>
                <h2 class="hph-text-2xl hph-font-bold hph-mb-md">No Articles Yet</h2>
                <p class="hph-text-gray-600 hph-mb-xl">
                    We're working on bringing you the latest real estate news and insights. Check back soon!
                </p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="hph-btn hph-btn-primary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
            
        <?php endif; ?>
        
        <!-- Newsletter & Categories Footer -->
        <?php if (have_posts()): ?>
            <div class="hph-blog-footer hph-mt-3xl hph-pt-2xl hph-border-t">
                <div class="hph-grid hph-lg:grid-cols-2 hph-gap-xl hph-items-center">
                    
                    <div class="hph-newsletter-signup">
                        <h3 class="hph-text-xl hph-font-bold hph-mb-md">Never Miss an Update</h3>
                        <p class="hph-text-gray-600 hph-mb-lg">
                            Subscribe to our newsletter for the latest real estate market insights, tips, and news.
                        </p>
                        
                        <form class="hph-newsletter-form hph-flex hph-gap-sm">
                            <input 
                                type="email" 
                                placeholder="Enter your email address" 
                                class="hph-form-input hph-flex-1"
                                required
                            >
                            <button type="submit" class="hph-btn hph-btn-primary">
                                <i class="fas fa-envelope"></i>
                                Subscribe
                            </button>
                        </form>
                    </div>
                    
                    <div class="hph-blog-categories">
                        <h3 class="hph-text-xl hph-font-bold hph-mb-md">Explore Topics</h3>
                        
                        <?php 
                        $categories = get_categories([
                            'orderby' => 'name',
                            'order' => 'ASC',
                            'hide_empty' => true,
                            'exclude' => [1] // Exclude "Uncategorized"
                        ]);
                        ?>
                        
                        <div class="hph-category-grid hph-grid hph-grid-cols-2 hph-gap-sm">
                            <?php foreach (array_slice($categories, 0, 8) as $category): ?>
                                <a 
                                    href="<?php echo esc_url(get_category_link($category)); ?>" 
                                    class="hph-category-card hph-flex hph-items-center hph-justify-between hph-p-sm hph-bg-gray-50 hph-rounded hph-text-sm hover:hph-bg-primary hover:hph-text-white hph-transition"
                                >
                                    <span><?php echo esc_html($category->name); ?></span>
                                    <span class="hph-category-count hph-text-xs"><?php echo $category->count; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (count($categories) > 8): ?>
                            <div class="hph-mt-md">
                                <a href="<?php echo esc_url(home_url('/categories')); ?>" class="hph-text-primary hover:hph-text-primary-dark">
                                    View All Categories <i class="fas fa-arrow-right hph-ml-xs"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
        <?php endif; ?>
        
    </div>
    
</main>

<?php
get_footer();