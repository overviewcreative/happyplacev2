<?php
/**
 * Main Index Template
 * 
 * Default template for the Happy Place theme using universal hero system
 *
 * @package HappyPlaceTheme
 */

get_header();

// Auto-detect post type for proper hero display
$current_post_type = '';
if (is_post_type_archive()) {
    $current_post_type = get_query_var('post_type');
} elseif (is_home() || is_category() || is_tag()) {
    $current_post_type = 'post';
} else {
    $current_post_type = get_post_type() ?: 'post';
}

// Get hero data with auto-detection, fallback to blog for posts
$hero_data = hpt_get_archive_hero_data($current_post_type, [
    'title' => $current_post_type === 'post' ? 'Blog Archive' : '',
    'subtitle' => $current_post_type === 'post' ? 'All our latest articles and insights' : ''
]);

?>

<main class="hph-main-content">
    
    <?php 
    // Universal hero section
    hpt_render_archive_hero($hero_data); 
    ?>
    
    <section class="hph-blog-archive">
        
        <div class="hph-container hph-py-2xl">
            
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
                
                <div class="hph-pagination-container hph-mt-2xl">
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
                    <i class="fas fa-search hph-text-6xl hph-text-gray-300 hph-mb-lg"></i>
                    <h2 class="hph-text-2xl hph-font-bold hph-mb-md">No Posts Found</h2>
                    <p class="hph-text-gray-600 hph-mb-xl">
                        We couldn't find any posts matching your request.
                    </p>
                    
                    <?php if (is_search()): ?>
                        <p class="hph-text-gray-600 hph-mb-lg">
                            Try searching for something else.
                        </p>
                        <div class="hph-search-form-wrapper">
                            <?php get_search_form(); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
            <?php endif; ?>
            
        </div>
        
    </section>
</main>

<?php
get_footer();
