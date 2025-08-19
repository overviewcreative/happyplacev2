<?php
/**
 * The template for displaying archive pages
 *
 * @package HappyPlaceTheme
 */

get_header(); ?>

<div class="hero hero-secondary">
    <div class="container">
        <div class="hero-content">
            <?php the_archive_title('<h1 class="archive-title">', '</h1>'); ?>
            <?php the_archive_description('<div class="archive-description text-muted">', '</div>'); ?>
        </div>
    </div>
</div>

<main id="primary" class="site-main">
    <div class="container">
        
        <div class="content-area">
            
            <div class="row">
                
                <!-- Main Content -->
                <div class="col-lg-8">
                    
                    <?php if (have_posts()) : ?>
                        
                        <div class="archive-content">
                            
                            <div class="posts-grid grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                <?php while (have_posts()) : the_post(); ?>
                                    
                                    <article id="post-<?php the_ID(); ?>" <?php post_class('card'); ?>>
                                        
                                        <?php if (has_post_thumbnail()) : ?>
                                            <div class="card-image">
                                                <a href="<?php the_permalink(); ?>">
                                                    <?php the_post_thumbnail('medium', array('class' => 'img-responsive')); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="card-body">
                                            
                                            <div class="post-meta text-sm text-muted mb-2">
                                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                                    <?php echo esc_html(get_the_date()); ?>
                                                </time>
                                                <?php if (get_post_type() === 'post') : ?>
                                                    <span class="meta-separator">•</span>
                                                    <span class="post-author">
                                                        <?php printf(esc_html__('By %s', 'happy-place-theme'), get_the_author()); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <h3 class="card-title">
                                                <a href="<?php the_permalink(); ?>" class="link-dark">
                                                    <?php the_title(); ?>
                                                </a>
                                            </h3>
                                            
                                            <div class="card-text">
                                                <?php the_excerpt(); ?>
                                            </div>
                                            
                                            <div class="card-footer">
                                                <a href="<?php the_permalink(); ?>" class="btn btn-outline btn-sm">
                                                    <?php esc_html_e('Read More', 'happy-place-theme'); ?>
                                                    <i class="fas fa-arrow-right ml-2"></i>
                                                </a>
                                                
                                                <?php if (get_post_type() === 'post' && has_category()) : ?>
                                                    <div class="post-categories mt-3">
                                                        <?php the_category(' '); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                        </div>
                                        
                                    </article>
                                    
                                <?php endwhile; ?>
                                
                            </div>
                            
                            <!-- Pagination -->
                            <div class="pagination-wrapper mt-8">
                                <?php
                                the_posts_pagination(array(
                                    'mid_size'  => 2,
                                    'prev_text' => '<i class="fas fa-chevron-left"></i> ' . __('Previous', 'happy-place-theme'),
                                    'next_text' => __('Next', 'happy-place-theme') . ' <i class="fas fa-chevron-right"></i>',
                                ));
                                ?>
                            </div>
                            
                        </div>
                        
                    <?php else : ?>
                        
                        <div class="no-content text-center py-12">
                            <div class="no-content-icon text-4xl text-muted mb-4">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <h2 class="no-content-title text-xl font-bold mb-4">
                                <?php esc_html_e('Nothing found', 'happy-place-theme'); ?>
                            </h2>
                            <p class="no-content-message text-muted mb-6">
                                <?php esc_html_e('It seems we can\'t find what you\'re looking for. Perhaps searching can help.', 'happy-place-theme'); ?>
                            </p>
                            <div class="search-form max-w-md mx-auto">
                                <?php get_search_form(); ?>
                            </div>
                        </div>
                        
                    <?php endif; ?>
                    
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <?php get_sidebar(); ?>
                </div>
                
            </div>
            
        </div>
        
    </div>
</main>

<?php get_footer(); ?>
