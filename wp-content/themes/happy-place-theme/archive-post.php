<?php
/**
 * Blog Archive Template
 * 
 * Archive template for blog posts using the universal hero system
 * 
 * @package Happy_Place_Theme
 */

get_header();

// Get hero data for blog posts
$hero_data = hpt_get_archive_hero_data('post');
?>

<main class="hph-main-content">
    
    <?php 
    // Universal hero section
    hpt_render_archive_hero($hero_data); 
    ?>
    
    <div class="hph-blog-archive-container hph-container hph-py-3xl">
        
        <?php if (have_posts()): ?>
            
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
            // Standard pagination for blog (keep pagination instead of load more for SEO)
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
            
        <?php else: ?>
            
            <div class="hph-empty-state hph-text-center hph-py-3xl">
                <i class="fas fa-newspaper hph-text-6xl hph-text-gray-300 hph-mb-lg"></i>
                <h2 class="hph-text-2xl hph-font-bold hph-mb-md">No Articles Found</h2>
                
                <?php if (is_search()): ?>
                    <p class="hph-text-gray-600 hph-mb-xl">
                        No articles found for "<strong><?php echo get_search_query(); ?></strong>". 
                        Try different keywords or browse our categories.
                    </p>
                <?php else: ?>
                    <p class="hph-text-gray-600 hph-mb-xl">
                        No articles match your current filters. Try adjusting your search criteria.
                    </p>
                <?php endif; ?>
                
                <div class="hph-empty-actions">
                    <a href="<?php echo esc_url(home_url('/blog')); ?>" class="hph-btn hph-btn-primary">
                        <i class="fas fa-home"></i> View All Articles
                    </a>
                    
                    <?php if (is_search() || is_category() || is_author()): ?>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="hph-btn hph-btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Home
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php endif; ?>
        
        <?php if (have_posts()): ?>
            <!-- Blog Sidebar/Newsletter Signup -->
            <div class="hph-blog-footer hph-mt-3xl hph-pt-2xl hph-border-t">
                <div class="hph-grid hph-lg:grid-cols-2 hph-gap-xl hph-items-center">
                    
                    <div class="hph-newsletter-signup">
                        <h3 class="hph-text-xl hph-font-bold hph-mb-md">Stay Updated</h3>
                        <p class="hph-text-gray-600 hph-mb-lg">
                            Get the latest real estate news and market insights delivered to your inbox.
                        </p>
                        
                        <!-- Newsletter signup form -->
                        <form class="hph-newsletter-form hph-flex hph-gap-sm">
                            <input 
                                type="email" 
                                placeholder="Enter your email address" 
                                class="hph-form-input hph-flex-1"
                                required
                            >
                            <button type="submit" class="hph-btn hph-btn-primary">
                                Subscribe
                            </button>
                        </form>
                    </div>
                    
                    <div class="hph-popular-categories">
                        <h3 class="hph-text-xl hph-font-bold hph-mb-md">Popular Topics</h3>
                        
                        <?php 
                        $popular_categories = get_categories([
                            'orderby' => 'count',
                            'order' => 'DESC',
                            'number' => 6,
                            'hide_empty' => true
                        ]);
                        ?>
                        
                        <div class="hph-category-links hph-flex hph-flex-wrap hph-gap-sm">
                            <?php foreach ($popular_categories as $category): ?>
                                <a 
                                    href="<?php echo esc_url(get_category_link($category)); ?>" 
                                    class="hph-category-tag hph-px-sm hph-py-xs hph-bg-gray-100 hph-rounded hph-text-sm hph-text-gray-700 hover:hph-bg-primary hover:hph-text-white hph-transition"
                                >
                                    <?php echo esc_html($category->name); ?>
                                    <span class="hph-category-count">(<?php echo $category->count; ?>)</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                </div>
            </div>
        <?php endif; ?>
        
    </div>
    
</main>

<?php
get_footer();