<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package HappyPlaceTheme
 */

get_header(); ?>

<div class="hero hero-secondary">
    <div class="container">
        <div class="hero-content text-center">
            <h1 class="page-title"><?php esc_html_e('Oops! That page can\'t be found.', 'happy-place-theme'); ?></h1>
            <p class="page-subtitle"><?php esc_html_e('It looks like nothing was found at this location.', 'happy-place-theme'); ?></p>
        </div>
    </div>
</div>

<main id="primary" class="site-main">
    <div class="container">
        
        <div class="error-404 text-center py-12">
            
            <div class="error-content max-w-2xl mx-auto">
                
                <div class="error-icon text-6xl text-muted mb-6">
                    <i class="fas fa-search"></i>
                </div>
                
                <h2 class="error-title text-2xl font-bold mb-4"><?php esc_html_e('Page Not Found', 'happy-place-theme'); ?></h2>
                
                <p class="error-message text-muted mb-8">
                    <?php esc_html_e('The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'happy-place-theme'); ?>
                </p>
                
                <div class="error-search mb-8">
                    <div class="search-form max-w-md mx-auto">
                        <?php get_search_form(); ?>
                    </div>
                </div>
                
                <div class="error-actions">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary mr-4">
                        <i class="fas fa-home mr-2"></i>
                        <?php esc_html_e('Go Home', 'happy-place-theme'); ?>
                    </a>
                    
                    <?php if (post_type_exists('listing')) : ?>
                        <a href="<?php echo esc_url(get_post_type_archive_link('listing')); ?>" class="btn btn-outline">
                            <i class="fas fa-building mr-2"></i>
                            <?php esc_html_e('Browse Properties', 'happy-place-theme'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                
            </div>
            
        </div>
        
        <!-- Suggested Content -->
        <div class="suggested-content mt-12">
            <div class="section-header text-center mb-8">
                <h3 class="section-title"><?php esc_html_e('You Might Be Interested In', 'happy-place-theme'); ?></h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Recent Posts -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"><?php esc_html_e('Recent Posts', 'happy-place-theme'); ?></h4>
                    </div>
                    <div class="card-body">
                        <?php
                        $recent_posts = wp_get_recent_posts(array(
                            'numberposts' => 3,
                            'post_status' => 'publish'
                        ));
                        
                        if ($recent_posts) : ?>
                            <ul class="space-y-3">
                                <?php foreach ($recent_posts as $post) : ?>
                                    <li>
                                        <a href="<?php echo esc_url(get_permalink($post['ID'])); ?>" class="link-primary">
                                            <?php echo esc_html($post['post_title']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <p class="text-muted"><?php esc_html_e('No recent posts found.', 'happy-place-theme'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Listings -->
                <?php if (post_type_exists('listing')) : ?>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><?php esc_html_e('Recent Properties', 'happy-place-theme'); ?></h4>
                        </div>
                        <div class="card-body">
                            <?php
                            $recent_listings = get_posts(array(
                                'post_type' => 'listing',
                                'posts_per_page' => 3,
                                'post_status' => 'publish'
                            ));
                            
                            if ($recent_listings) : ?>
                                <ul class="space-y-3">
                                    <?php foreach ($recent_listings as $listing) : ?>
                                        <li>
                                            <a href="<?php echo esc_url(get_permalink($listing->ID)); ?>" class="link-primary">
                                                <?php echo esc_html($listing->post_title); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else : ?>
                                <p class="text-muted"><?php esc_html_e('No recent listings found.', 'happy-place-theme'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Popular Pages -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"><?php esc_html_e('Popular Pages', 'happy-place-theme'); ?></h4>
                    </div>
                    <div class="card-body">
                        <ul class="space-y-3">
                            <li><a href="<?php echo esc_url(home_url('/')); ?>" class="link-primary"><?php esc_html_e('Home', 'happy-place-theme'); ?></a></li>
                            <li><a href="<?php echo esc_url(home_url('/about')); ?>" class="link-primary"><?php esc_html_e('About Us', 'happy-place-theme'); ?></a></li>
                            <li><a href="<?php echo esc_url(home_url('/contact')); ?>" class="link-primary"><?php esc_html_e('Contact', 'happy-place-theme'); ?></a></li>
                            <?php if (post_type_exists('agent')) : ?>
                                <li><a href="<?php echo esc_url(get_post_type_archive_link('agent')); ?>" class="link-primary"><?php esc_html_e('Our Agents', 'happy-place-theme'); ?></a></li>
                            <?php endif; ?>
                            <?php if (post_type_exists('community')) : ?>
                                <li><a href="<?php echo esc_url(get_post_type_archive_link('community')); ?>" class="link-primary"><?php esc_html_e('Communities', 'happy-place-theme'); ?></a></li>
                            <?php endif; ?>
                            <?php if (post_type_exists('open-house')) : ?>
                                <li><a href="<?php echo esc_url(get_post_type_archive_link('open-house')); ?>" class="link-primary"><?php esc_html_e('Open Houses', 'happy-place-theme'); ?></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
</main>

<?php get_footer(); ?>
