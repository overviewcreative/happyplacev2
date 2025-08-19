<?php
/**
 * The template for displaying all pages
 *
 * @package HappyPlaceTheme
 */

get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
    
    <div class="hero hero-secondary">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="page-title"><?php the_title(); ?></h1>
                
                <?php if (has_excerpt()) : ?>
                    <p class="page-subtitle"><?php the_excerpt(); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <main id="primary" class="site-main">
        <div class="container">
            
            <article id="post-<?php the_ID(); ?>" <?php post_class('page-content'); ?>>
                
                <?php if (has_post_thumbnail()) : ?>
                    <div class="page-featured-image mb-8">
                        <?php the_post_thumbnail('large', array('class' => 'img-cover rounded')); ?>
                    </div>
                <?php endif; ?>
                
                <div class="page-content-wrapper">
                    <?php the_content(); ?>
                    
                    <?php
                    wp_link_pages(array(
                        'before' => '<div class="page-links mt-6"><span class="page-links-title font-medium">' . esc_html__('Pages:', 'happy-place-theme') . '</span>',
                        'after'  => '</div>',
                    ));
                    ?>
                </div>
                
                <?php if (comments_open() || get_comments_number()) : ?>
                    <div class="page-comments mt-12 pt-8 border-t">
                        <?php comments_template(); ?>
                    </div>
                <?php endif; ?>
                
            </article>
            
        </div>
    </main>
    
<?php endwhile; ?>

<?php get_footer(); ?>
