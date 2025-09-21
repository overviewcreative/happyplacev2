<?php
/**
 * Archive template for blog posts
 * 
 * @package Happy_Place_Theme
 */

get_header();

// Get hero data
$hero_data = hpt_get_archive_hero_data('blog_post');
?>

<main class="hph-main-content">
    
    <?php 
    // Universal hero section
    hpt_render_archive_hero($hero_data); 
    ?>
    
    <section class="hph-archive-content">
        <div class="hph-container hph-py-2xl">
            
            <?php if (have_posts()): ?>
                
                <!-- Filter/Search Results Info -->
                <?php if (is_search() || !empty($_GET)): ?>
                    <div class="hph-search-results-info hph-mb-xl">
                        <div class="hph-flex hph-items-center hph-justify-between hph-flex-wrap hph-gap-md">
                            <div class="hph-results-count">
                                <span class="hph-text-gray-600">
                                    <?php
                                    global $wp_query;
                                    $total = $wp_query->found_posts;
                                    printf(
                                        _n('Found %s blog post', 'Found %s blog posts', $total, 'happy-place-theme'),
                                        '<strong>' . number_format($total) . '</strong>'
                                    );
                                    ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($_GET)): ?>
                                <a href="<?php echo esc_url(get_post_type_archive_link('blog_post')); ?>" 
                                   class="hph-btn hph-btn-outline hph-btn-sm">
                                    <i class="fas fa-times hph-mr-xs"></i>
                                    Clear Filters
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Posts Grid -->
                <div class="hph-posts-grid hph-grid hph-grid-auto-fit-md hph-gap-lg hph-mb-2xl">
                    <?php while (have_posts()): the_post(); ?>
                        <?php 
                        // Use universal card system
                        get_template_part('template-parts/cards/universal-card', null, [
                            'post_type' => 'blog_post',
                            'post_id' => get_the_ID()
                        ]); 
                        ?>
                    <?php endwhile; ?>
                </div>
                
                <!-- Pagination -->
                <div class="hph-pagination-container">
                    <?php
                    the_posts_pagination([
                        'mid_size' => 2,
                        'prev_text' => '<i class="fas fa-chevron-left"></i> Previous',
                        'next_text' => 'Next <i class="fas fa-chevron-right"></i>',
                        'screen_reader_text' => 'Blog posts navigation'
                    ]);
                    ?>
                </div>
                
            <?php else: ?>
                
                <!-- Empty State -->
                <div class="hph-empty-state hph-text-center hph-py-3xl">
                    <i class="fas fa-newspaper hph-text-6xl hph-text-gray-300 hph-mb-lg"></i>
                    
                    <?php if (is_search()): ?>
                        <h2 class="hph-text-2xl hph-font-bold hph-mb-md">No Blog Posts Found</h2>
                        <p class="hph-text-gray-600 hph-mb-xl">
                            We couldn't find any blog posts matching your search criteria.
                        </p>
                        <div class="hph-flex hph-gap-md hph-justify-center">
                            <a href="<?php echo esc_url(get_post_type_archive_link('blog_post')); ?>" 
                               class="hph-btn hph-btn-primary">
                                <i class="fas fa-newspaper"></i> View All Posts
                            </a>
                            <button type="button" 
                                    onclick="document.querySelector('.hph-hero-search-form input[name=&quot;s&quot;]').value=''; document.querySelector('.hph-hero-search-form').submit();" 
                                    class="hph-btn hph-btn-outline">
                                <i class="fas fa-times"></i> Clear Search
                            </button>
                        </div>
                    <?php else: ?>
                        <h2 class="hph-text-2xl hph-font-bold hph-mb-md">No Blog Posts Yet</h2>
                        <p class="hph-text-gray-600 hph-mb-xl">
                            We're working on bringing you great content. Check back soon!
                        </p>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="hph-btn hph-btn-primary">
                            <i class="fas fa-home"></i> Back to Home
                        </a>
                    <?php endif; ?>
                </div>
                
            <?php endif; ?>
            
        </div>
    </section>
    
    <?php if (have_posts()): ?>
        <!-- Related Content Section -->
        <section class="hph-related-content hph-bg-gray-50 hph-py-2xl">
            <div class="hph-container">
                <div class="hph-grid hph-lg:grid-cols-2 hph-gap-xl">
                    
                    <!-- Popular Categories -->
                    <div class="hph-popular-categories">
                        <h3 class="hph-text-xl hph-font-bold hph-mb-lg">Popular Categories</h3>
                        
                        <?php 
                        $categories = get_terms([
                            'taxonomy' => 'blog_category',
                            'orderby' => 'count',
                            'order' => 'DESC',
                            'number' => 6,
                            'hide_empty' => true
                        ]);
                        ?>
                        
                        <?php if ($categories && !is_wp_error($categories)): ?>
                            <div class="hph-category-grid hph-grid hph-sm:grid-cols-2 hph-gap-sm">
                                <?php foreach ($categories as $category): ?>
                                    <a href="<?php echo esc_url(get_term_link($category)); ?>" 
                                       class="hph-category-card hph-flex hph-items-center hph-justify-between hph-p-md hph-bg-white hph-rounded hph-text-sm hover:hph-bg-primary hover:hph-text-white hph-transition">
                                        <span><?php echo esc_html($category->name); ?></span>
                                        <span class="hph-category-count hph-text-xs hph-opacity-75">
                                            <?php echo $category->count; ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Newsletter Signup -->
                    <div class="hph-newsletter-signup">
                        <h3 class="hph-text-xl hph-font-bold hph-mb-lg">Stay Updated</h3>
                        <p class="hph-text-gray-600 hph-mb-lg">
                            Subscribe to our newsletter for the latest blog posts and updates.
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
                    
                </div>
            </div>
        </section>
    <?php endif; ?>
    
</main>

<?php
get_footer();