<?php
/**
 * The template for displaying all single posts
 *
 * @package HappyPlaceTheme
 */

get_header(); ?>

<div class="hero hero-secondary">
    <div class="container">
        <div class="hero-content">
            <nav class="breadcrumb" aria-label="breadcrumb">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item">
                        <a href="<?php echo esc_url(home_url()); ?>" class="breadcrumb-link"><?php esc_html_e('Home', 'happy-place-theme'); ?></a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="breadcrumb-link"><?php esc_html_e('Blog', 'happy-place-theme'); ?></a>
                    </li>
                    <li class="breadcrumb-item active"><?php the_title(); ?></li>
                </ol>
            </nav>
            
            <h1 class="page-title"><?php the_title(); ?></h1>
        </div>
    </div>
</div>

<main id="primary" class="site-main">
    <div class="container">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2">
                
                <?php while (have_posts()) : the_post(); ?>
                    
                    <article id="post-<?php the_ID(); ?>" <?php post_class('card post-single'); ?>>
                        
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="card-image">
                                <?php the_post_thumbnail('large', array('class' => 'img-cover')); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            
                            <div class="post-meta mb-4">
                                <div class="meta-items flex flex-wrap items-center gap-4 text-sm text-muted">
                                    <span class="meta-date">
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?php echo get_the_date(); ?>
                                    </span>
                                    
                                    <span class="meta-author">
                                        <i class="fas fa-user mr-1"></i>
                                        <?php the_author(); ?>
                                    </span>
                                    
                                    <?php if (has_category()) : ?>
                                        <span class="meta-categories">
                                            <i class="fas fa-folder mr-1"></i>
                                            <?php the_category(', '); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="post-content">
                                <?php the_content(); ?>
                                
                                <?php
                                wp_link_pages(array(
                                    'before' => '<div class="page-links mt-6"><span class="page-links-title font-medium">' . esc_html__('Pages:', 'happy-place-theme') . '</span>',
                                    'after'  => '</div>',
                                ));
                                ?>
                            </div>
                            
                            <?php if (has_tag()) : ?>
                                <div class="post-tags mt-6 pt-6 border-t">
                                    <div class="tags-list">
                                        <?php the_tags('<div class="flex flex-wrap gap-2">', '', '</div>'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                        </div>
                        
                    </article>
                    
                    <?php
                    // Post navigation
                    the_post_navigation(array(
                        'prev_text' => '<i class="fas fa-chevron-left mr-2"></i>' . esc_html__('Previous Post', 'happy-place-theme'),
                        'next_text' => esc_html__('Next Post', 'happy-place-theme') . '<i class="fas fa-chevron-right ml-2"></i>',
                        'class' => 'post-navigation mt-8',
                    ));
                    ?>
                    
                    <?php
                    // Comments
                    if (comments_open() || get_comments_number()) {
                        echo '<div class="comments-section mt-8">';
                        comments_template();
                        echo '</div>';
                    }
                    ?>
                    
                <?php endwhile; ?>
                
            </div>
            
            <!-- Sidebar -->
            <div class="sidebar">
                <?php get_sidebar(); ?>
            </div>
            
        </div>
    </div>
</main>

<?php get_footer(); ?>
