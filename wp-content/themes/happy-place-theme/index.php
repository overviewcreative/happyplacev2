<?php
/**
 * Main Index Template
 * 
 * Default template for the Happy Place theme
 *
 * @package HappyPlaceTheme
 */

get_header(); ?>

<!-- Main Content Section -->
<main class="section">
    <div class="section-container">
        
        <?php if (have_posts()) : ?>
            
            <!-- Page Header -->
            <?php if (is_home() && !is_front_page()) : ?>
                <header class="article-header">
                    <h1 class="article-title"><?php single_post_title(); ?></h1>
                </header>
            <?php endif; ?>
            
            <!-- Blog Grid -->
            <div class="blog-grid" style="margin-bottom: var(--hph-space-3xl);">
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('blog-card'); ?>>
                        
                        <!-- Featured Image -->
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="blog-card-image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium', array('loading' => 'lazy')); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Card Content -->
                        <div class="blog-card-content">
                            
                            <!-- Category -->
                            <?php if (has_category()) : ?>
                                <div class="blog-card-category">
                                    <?php the_category(', '); ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Title -->
                            <h2 class="blog-card-title">
                                <a href="<?php the_permalink(); ?>" 
                                   style="color: var(--hph-gray-900); text-decoration: none; transition: var(--hph-transition-fast);"
                                   onmouseover="this.style.color='var(--hph-primary)'"
                                   onmouseout="this.style.color='var(--hph-gray-900)'">
                                   <?php the_title(); ?>
                                </a>
                            </h2>
                            
                            <!-- Excerpt -->
                            <div class="blog-card-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            
                            <!-- Card Footer -->
                            <div class="blog-card-footer">
                                <div class="blog-card-author">
                                    <?php if (get_avatar(get_the_author_meta('ID'), 32)) : ?>
                                        <div class="blog-card-author-avatar">
                                            <?php echo get_avatar(get_the_author_meta('ID'), 32, '', '', array('loading' => 'lazy')); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="blog-card-author-name">
                                        <?php esc_html_e('by', 'happy-place-theme'); ?> 
                                        <?php the_author(); ?>
                                    </div>
                                </div>
                                
                                <time class="blog-card-date" datetime="<?php echo get_the_date('c'); ?>">
                                    <?php echo get_the_date(); ?>
                                </time>
                            </div>
                        </div>
                        
                    </article>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <nav class="pagination-wrap" style="text-align: center;">
                <?php
                the_posts_pagination(array(
                    'prev_text' => '<i class="fas fa-chevron-left"></i> ' . esc_html__('Previous', 'happy-place-theme'),
                    'next_text' => esc_html__('Next', 'happy-place-theme') . ' <i class="fas fa-chevron-right"></i>',
                    'before_page_number' => '<span class="screen-reader-text">' . esc_html__('Page', 'happy-place-theme') . ' </span>',
                ));
                ?>
            </nav>
            
        <?php else : ?>
            
            <!-- No Posts Found -->
            <div class="content-section" style="text-align: center; padding: var(--hph-space-5xl) 0;">
                <h2 style="font-size: var(--hph-text-3xl); font-weight: 700; color: var(--hph-gray-900); margin-bottom: var(--hph-space-lg);">
                    <?php esc_html_e('Nothing Found', 'happy-place-theme'); ?>
                </h2>
                <p style="font-size: var(--hph-text-lg); color: var(--hph-gray-600); margin-bottom: var(--hph-space-xl);">
                    <?php esc_html_e('It looks like nothing was found at this location.', 'happy-place-theme'); ?>
                </p>
                
                <?php if (is_search()) : ?>
                    <p style="color: var(--hph-gray-600); margin-bottom: var(--hph-space-lg);">
                        <?php esc_html_e('Try searching for something else.', 'happy-place-theme'); ?>
                    </p>
                    <div class="search-form-wrapper">
                        <?php get_search_form(); ?>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php endif; ?>
        
    </div>
</main>

<?php get_footer(); ?>
