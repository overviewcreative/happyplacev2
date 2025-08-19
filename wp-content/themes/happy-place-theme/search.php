<?php
/**
 * The template for displaying search results pages
 *
 * @package HappyPlaceTheme
 */

get_header(); ?>

<div class="hero hero-secondary">
    <div class="container">
        <div class="hero-content">
            <h1 class="page-title">
                <?php
                printf(
                    esc_html__('Search Results for: %s', 'happy-place-theme'),
                    '<span class="search-term">' . esc_html(get_search_query()) . '</span>'
                );
                ?>
            </h1>
            <?php if (have_posts()) : ?>
                <p class="page-subtitle">
                    <?php
                    global $wp_query;
                    printf(
                        esc_html(_n('Found %d result', 'Found %d results', $wp_query->found_posts, 'happy-place-theme')),
                        $wp_query->found_posts
                    );
                    ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<main id="primary" class="site-main">
    <div class="container">
        
        <div class="content-area">
            
            <div class="row">
                
                <!-- Main Content -->
                <div class="col-lg-8">
                    
                    <!-- Search Form -->
                    <div class="search-form-wrapper mb-8">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title"><?php esc_html_e('Refine Your Search', 'happy-place-theme'); ?></h3>
                                <?php get_search_form(); ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (have_posts()) : ?>
                        
                        <div class="search-results">
                            
                            <div class="results-list space-y-6">
                                
                                <?php while (have_posts()) : the_post(); ?>
                                    
                                    <article id="post-<?php the_ID(); ?>" <?php post_class('card search-result'); ?>>
                                        
                                        <div class="card-body">
                                            
                                            <div class="row">
                                                
                                                <?php if (has_post_thumbnail()) : ?>
                                                    <div class="col-md-3">
                                                        <div class="result-thumbnail">
                                                            <a href="<?php the_permalink(); ?>">
                                                                <?php the_post_thumbnail('thumbnail', array('class' => 'img-responsive')); ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-9">
                                                <?php else : ?>
                                                    <div class="col-12">
                                                <?php endif; ?>
                                                
                                                    <div class="result-content">
                                                        
                                                        <div class="result-meta text-sm text-muted mb-2">
                                                            <span class="result-type badge badge-outline">
                                                                <?php echo esc_html(get_post_type_object(get_post_type())->labels->singular_name); ?>
                                                            </span>
                                                            <span class="meta-separator">•</span>
                                                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                                                <?php echo esc_html(get_the_date()); ?>
                                                            </time>
                                                            <?php if (get_post_type() === 'post') : ?>
                                                                <span class="meta-separator">•</span>
                                                                <span class="result-author">
                                                                    <?php printf(esc_html__('By %s', 'happy-place-theme'), get_the_author()); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <h3 class="result-title">
                                                            <a href="<?php the_permalink(); ?>" class="link-dark">
                                                                <?php the_title(); ?>
                                                            </a>
                                                        </h3>
                                                        
                                                        <div class="result-excerpt">
                                                            <?php
                                                            $excerpt = get_the_excerpt();
                                                            $search_query = get_search_query();
                                                            
                                                            // Highlight search terms in excerpt
                                                            if ($search_query && $excerpt) {
                                                                $highlighted_excerpt = preg_replace(
                                                                    '/(' . preg_quote($search_query, '/') . ')/i',
                                                                    '<mark>$1</mark>',
                                                                    $excerpt
                                                                );
                                                                echo wp_kses_post($highlighted_excerpt);
                                                            } else {
                                                                echo wp_kses_post($excerpt);
                                                            }
                                                            ?>
                                                        </div>
                                                        
                                                        <div class="result-actions mt-3">
                                                            <a href="<?php the_permalink(); ?>" class="btn btn-outline btn-sm">
                                                                <?php esc_html_e('View Details', 'happy-place-theme'); ?>
                                                                <i class="fas fa-arrow-right ml-2"></i>
                                                            </a>
                                                            
                                                            <?php if (get_post_type() === 'post' && has_category()) : ?>
                                                                <div class="result-categories mt-2">
                                                                    <?php the_category(' '); ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                    </div>
                                                    
                                                </div>
                                                
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
                        
                        <div class="no-results text-center py-12">
                            <div class="no-results-icon text-6xl text-muted mb-6">
                                <i class="fas fa-search"></i>
                            </div>
                            <h2 class="no-results-title text-2xl font-bold mb-4">
                                <?php esc_html_e('Nothing found', 'happy-place-theme'); ?>
                            </h2>
                            <p class="no-results-message text-muted mb-8">
                                <?php esc_html_e('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'happy-place-theme'); ?>
                            </p>
                            
                            <!-- Search Tips -->
                            <div class="search-tips max-w-2xl mx-auto">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title"><?php esc_html_e('Search Tips', 'happy-place-theme'); ?></h3>
                                    </div>
                                    <div class="card-body text-left">
                                        <ul class="list-disc pl-6 space-y-2">
                                            <li><?php esc_html_e('Check your spelling and try again', 'happy-place-theme'); ?></li>
                                            <li><?php esc_html_e('Try using fewer or different keywords', 'happy-place-theme'); ?></li>
                                            <li><?php esc_html_e('Try more general keywords', 'happy-place-theme'); ?></li>
                                            <li><?php esc_html_e('Try browsing our categories instead', 'happy-place-theme'); ?></li>
                                        </ul>
                                    </div>
                                </div>
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
