<?php
/**
 * The template for displaying city archives
 *
 * @package HappyPlaceTheme
 */

get_header(); ?>

<div class="hero hero-secondary">
    <div class="container">
        <div class="hero-content">
            <h1 class="archive-title"><?php esc_html_e('Cities We Serve', 'happy-place-theme'); ?></h1>
            <p class="archive-description text-muted">
                <?php esc_html_e('Discover the cities and areas where our expertise can help you find your perfect home', 'happy-place-theme'); ?>
            </p>
        </div>
    </div>
</div>

<main id="primary" class="site-main">
    <div class="container">
        
        <div class="content-area">
            
            <?php if (have_posts()) : ?>
                
                <div class="archive-meta mb-6">
                    <div class="results-count">
                        <?php
                        global $wp_query;
                        printf(
                            esc_html(_n('Serving %d city', 'Serving %d cities', $wp_query->found_posts, 'happy-place-theme')),
                            $wp_query->found_posts
                        );
                        ?>
                    </div>
                </div>
                
                <div class="cities-container">
                    <div class="cities-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        
                        <?php while (have_posts()) : the_post(); ?>
                            
                            <div class="city-item">
                                <div class="card city-card">
                                    
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="card-image">
                                            <a href="<?php the_permalink(); ?>">
                                                <?php the_post_thumbnail('medium', array('class' => 'img-responsive')); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="card-body">
                                        
                                        <h3 class="card-title">
                                            <a href="<?php the_permalink(); ?>" class="link-dark">
                                                <?php the_title(); ?>
                                            </a>
                                        </h3>
                                        
                                        <!-- City Stats -->
                                        <div class="city-stats mb-3">
                                            <div class="row text-center">
                                                <div class="col-6">
                                                    <div class="stat-item">
                                                        <?php
                                                        // Count listings in this city
                                                        $listings_count = 0;
                                                        if (function_exists('hpt_get_city_listings_count')) {
                                                            $listings_count = hpt_get_city_listings_count(get_the_ID());
                                                        } else {
                                                            // Fallback query
                                                            $listings_query = new WP_Query(array(
                                                                'post_type' => 'listing',
                                                                'posts_per_page' => -1,
                                                                'post_status' => 'publish',
                                                                'meta_query' => array(
                                                                    array(
                                                                        'key' => 'city',
                                                                        'value' => get_the_ID(),
                                                                        'compare' => '='
                                                                    )
                                                                )
                                                            ));
                                                            $listings_count = $listings_query->found_posts;
                                                            wp_reset_postdata();
                                                        }
                                                        ?>
                                                        <div class="stat-number font-bold text-primary"><?php echo esc_html($listings_count); ?></div>
                                                        <div class="stat-label text-xs text-muted"><?php esc_html_e('Properties', 'happy-place-theme'); ?></div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="stat-item">
                                                        <?php
                                                        // Count communities in this city
                                                        $communities_count = 0;
                                                        if (post_type_exists('community')) {
                                                            $communities_query = new WP_Query(array(
                                                                'post_type' => 'community',
                                                                'posts_per_page' => -1,
                                                                'post_status' => 'publish',
                                                                'meta_query' => array(
                                                                    array(
                                                                        'key' => 'city',
                                                                        'value' => get_the_ID(),
                                                                        'compare' => '='
                                                                    )
                                                                )
                                                            ));
                                                            $communities_count = $communities_query->found_posts;
                                                            wp_reset_postdata();
                                                        }
                                                        ?>
                                                        <div class="stat-number font-bold text-primary"><?php echo esc_html($communities_count); ?></div>
                                                        <div class="stat-label text-xs text-muted"><?php esc_html_e('Communities', 'happy-place-theme'); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- City Info -->
                                        <?php 
                                        $population = get_field('population');
                                        $state = get_field('state');
                                        ?>
                                        <div class="city-info mb-3">
                                            <?php if ($state) : ?>
                                                <div class="city-state text-muted mb-1">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                                    <?php echo esc_html($state); ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($population) : ?>
                                                <div class="city-population text-muted">
                                                    <i class="fas fa-users mr-1"></i>
                                                    <?php printf(esc_html__('Population: %s', 'happy-place-theme'), number_format($population)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Description -->
                                        <div class="card-text">
                                            <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="card-actions mt-4">
                                            <a href="<?php the_permalink(); ?>" class="btn btn-primary btn-sm">
                                                <?php esc_html_e('Learn More', 'happy-place-theme'); ?>
                                                <i class="fas fa-arrow-right ml-2"></i>
                                            </a>
                                            
                                            <?php if ($listings_count > 0) : ?>
                                                <a href="<?php echo esc_url(add_query_arg('city', get_the_ID(), get_post_type_archive_link('listing'))); ?>" class="btn btn-outline btn-sm ml-2">
                                                    <?php esc_html_e('View Properties', 'happy-place-theme'); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        
                                    </div>
                                    
                                </div>
                            </div>
                            
                        <?php endwhile; ?>
                        
                    </div>
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
                
            <?php else : ?>
                
                <div class="no-cities text-center py-12">
                    <div class="no-cities-icon text-6xl text-muted mb-6">
                        <i class="fas fa-city"></i>
                    </div>
                    <h2 class="no-cities-title text-2xl font-bold mb-4">
                        <?php esc_html_e('No Cities Found', 'happy-place-theme'); ?>
                    </h2>
                    <p class="no-cities-message text-muted mb-8">
                        <?php esc_html_e('We are always expanding our service areas. Contact us to learn about opportunities in your city.', 'happy-place-theme'); ?>
                    </p>
                    
                    <div class="no-cities-actions">
                        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn btn-primary mr-4">
                            <i class="fas fa-phone mr-2"></i>
                            <?php esc_html_e('Contact Us', 'happy-place-theme'); ?>
                        </a>
                        
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-outline">
                            <i class="fas fa-home mr-2"></i>
                            <?php esc_html_e('Back to Home', 'happy-place-theme'); ?>
                        </a>
                    </div>
                </div>
                
            <?php endif; ?>
            
        </div>
        
    </div>
</main>

<?php get_footer(); ?>
