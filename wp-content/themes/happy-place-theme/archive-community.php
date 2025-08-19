<?php
/**
 * The template for displaying community archives
 *
 * @package HappyPlaceTheme
 */

get_header(); ?>

<div class="hero hero-secondary">
    <div class="container">
        <div class="hero-content">
            <h1 class="archive-title"><?php esc_html_e('Communities', 'happy-place-theme'); ?></h1>
            <p class="archive-description text-muted">
                <?php esc_html_e('Explore the neighborhoods and communities where we serve', 'happy-place-theme'); ?>
            </p>
        </div>
    </div>
</div>

<main id="primary" class="site-main">
    <div class="container">
        
        <!-- Community Filters -->
        <div class="community-filters mb-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="community-search" placeholder="<?php esc_attr_e('Search communities...', 'happy-place-theme'); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="city-filter">
                                <option value=""><?php esc_html_e('All Cities', 'happy-place-theme'); ?></option>
                                <!-- Populated from city post type -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="price-range-filter">
                                <option value=""><?php esc_html_e('Any Price Range', 'happy-place-theme'); ?></option>
                                <option value="affordable"><?php esc_html_e('Under $300K', 'happy-place-theme'); ?></option>
                                <option value="moderate"><?php esc_html_e('$300K - $600K', 'happy-place-theme'); ?></option>
                                <option value="upscale"><?php esc_html_e('$600K - $1M', 'happy-place-theme'); ?></option>
                                <option value="luxury"><?php esc_html_e('Over $1M', 'happy-place-theme'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100" id="apply-filters">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content-area">
            
            <?php if (have_posts()) : ?>
                
                <div class="archive-meta mb-6">
                    <div class="results-count">
                        <?php
                        global $wp_query;
                        printf(
                            esc_html(_n('Discover %d community', 'Discover %d communities', $wp_query->found_posts, 'happy-place-theme')),
                            $wp_query->found_posts
                        );
                        ?>
                    </div>
                </div>
                
                <div class="communities-container">
                    <div class="communities-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        
                        <?php while (have_posts()) : the_post(); ?>
                            
                            <div class="community-item">
                                <div class="card community-card">
                                    
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="card-image">
                                            <a href="<?php the_permalink(); ?>">
                                                <?php the_post_thumbnail('medium_large', array('class' => 'img-responsive')); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="card-body">
                                        
                                        <h3 class="card-title">
                                            <a href="<?php the_permalink(); ?>" class="link-dark">
                                                <?php the_title(); ?>
                                            </a>
                                        </h3>
                                        
                                        <!-- Community Stats -->
                                        <div class="community-stats mb-3">
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <div class="stat-item">
                                                        <?php
                                                        // Count listings in this community
                                                        $listings_count = 0;
                                                        if (function_exists('hpt_get_community_listings_count')) {
                                                            $listings_count = hpt_get_community_listings_count(get_the_ID());
                                                        }
                                                        ?>
                                                        <div class="stat-number" style="font-weight: 700; color: var(--hph-primary-600);"><?php echo esc_html($listings_count); ?></div>
                                                        <div class="stat-label" style="font-size: var(--hph-text-xs); color: var(--hph-gray-500);"><?php esc_html_e('Listings', 'happy-place-theme'); ?></div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="stat-item">
                                                        <?php
                                                        $avg_price = get_field('average_home_price');
                                                        if ($avg_price) {
                                                            echo '<div class="stat-number font-bold text-primary">$' . esc_html(number_format($avg_price / 1000)) . 'K</div>';
                                                        } else {
                                                            echo '<div class="stat-number font-bold text-muted">—</div>';
                                                        }
                                                        ?>
                                                        <div class="stat-label text-xs text-muted"><?php esc_html_e('Avg Price', 'happy-place-theme'); ?></div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="stat-item">
                                                        <?php
                                                        $schools_rating = get_field('school_rating');
                                                        if ($schools_rating) {
                                                            echo '<div class="stat-number font-bold text-primary">' . esc_html($schools_rating) . '/10</div>';
                                                        } else {
                                                            echo '<div class="stat-number font-bold text-muted">—</div>';
                                                        }
                                                        ?>
                                                        <div class="stat-label text-xs text-muted"><?php esc_html_e('Schools', 'happy-place-theme'); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Community Features -->
                                        <?php 
                                        $features = get_field('community_features');
                                        if ($features && is_array($features)) : ?>
                                            <div class="community-features mb-3">
                                                <div class="features-list">
                                                    <?php foreach (array_slice($features, 0, 3) as $feature) : ?>
                                                        <span class="badge badge-light mr-1 mb-1"><?php echo esc_html($feature); ?></span>
                                                    <?php endforeach; ?>
                                                    <?php if (count($features) > 3) : ?>
                                                        <span class="text-muted text-sm">+<?php echo esc_html(count($features) - 3); ?> more</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Description -->
                                        <div class="card-text">
                                            <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="card-actions mt-4">
                                            <a href="<?php the_permalink(); ?>" class="btn btn-primary">
                                                <?php esc_html_e('Explore Community', 'happy-place-theme'); ?>
                                                <i class="fas fa-arrow-right ml-2"></i>
                                            </a>
                                            
                                            <?php if ($listings_count > 0) : ?>
                                                <a href="<?php echo esc_url(add_query_arg('community', get_the_ID(), get_post_type_archive_link('listing'))); ?>" class="btn btn-outline btn-sm ml-2">
                                                    <?php esc_html_e('View Listings', 'happy-place-theme'); ?>
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
                
                <div class="no-communities text-center py-12">
                    <div class="no-communities-icon text-6xl text-muted mb-6">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h2 class="no-communities-title text-2xl font-bold mb-4">
                        <?php esc_html_e('No Communities Found', 'happy-place-theme'); ?>
                    </h2>
                    <p class="no-communities-message text-muted mb-8">
                        <?php esc_html_e('We don\'t have any communities matching your search criteria. Please try different filters.', 'happy-place-theme'); ?>
                    </p>
                    
                    <div class="no-communities-actions">
                        <?php if (post_type_exists('listing')) : ?>
                            <a href="<?php echo esc_url(get_post_type_archive_link('listing')); ?>" class="btn btn-primary mr-4">
                                <i class="fas fa-home mr-2"></i>
                                <?php esc_html_e('Browse All Properties', 'happy-place-theme'); ?>
                            </a>
                        <?php endif; ?>
                        
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
