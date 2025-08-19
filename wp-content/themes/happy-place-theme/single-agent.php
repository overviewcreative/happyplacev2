<?php
/**
 * The template for displaying single agent posts
 *
 * @package HappyPlaceTheme
 */

get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
    
    <!-- Agent Hero -->
    <div class="agent-hero">
        <?php if (function_exists('hpt_get_agent_cover_photo')) : 
            $cover_photo = hpt_get_agent_cover_photo(get_the_ID());
            if ($cover_photo) : ?>
                <div class="hero-image">
                    <img src="<?php echo esc_url($cover_photo); ?>" alt="<?php the_title(); ?>" class="img-responsive w-full h-96 object-cover">
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="hero-overlay">
            <div class="container">
                <div class="hero-content text-white">
                    <div class="agent-intro">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if (function_exists('hpt_get_agent_photo')) : 
                                    $profile_photo = hpt_get_agent_photo(get_the_ID());
                                    if ($profile_photo) : ?>
                                        <img src="<?php echo esc_url($profile_photo); ?>" alt="<?php the_title(); ?>" class="agent-avatar rounded-circle">
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-10">
                                <h1 class="agent-name text-4xl font-bold mb-2"><?php the_title(); ?></h1>
                                
                                <?php if (function_exists('hpt_get_agent_title')) : 
                                    $agent_title = hpt_get_agent_title(get_the_ID());
                                    if ($agent_title) : ?>
                                        <p class="agent-title text-xl mb-3"><?php echo esc_html($agent_title); ?></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <div class="agent-contact">
                                    <?php if (function_exists('hpt_get_agent_phone')) : 
                                        $phone = hpt_get_agent_phone(get_the_ID());
                                        if ($phone) : ?>
                                            <a href="tel:<?php echo esc_attr($phone); ?>" class="btn btn-primary mr-3">
                                                <i class="fas fa-phone mr-2"></i>
                                                <?php echo esc_html($phone); ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php if (function_exists('hpt_get_agent_email')) : 
                                        $email = hpt_get_agent_email(get_the_ID());
                                        if ($email) : ?>
                                            <a href="mailto:<?php echo esc_attr($email); ?>" class="btn btn-outline btn-light">
                                                <i class="fas fa-envelope mr-2"></i>
                                                <?php esc_html_e('Email Me', 'happy-place-theme'); ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <main id="primary" class="site-main">
        <div class="container">
            
            <div class="agent-content">
                <div class="row">
                    
                    <!-- Main Content -->
                    <div class="col-lg-8">
                        
                        <!-- Agent Bio -->
                        <section class="agent-bio section">
                            <h2 class="section-title"><?php esc_html_e('About Me', 'happy-place-theme'); ?></h2>
                            <div class="bio-content">
                                <?php 
                                if (function_exists('hpt_get_agent_bio')) {
                                    $bio = hpt_get_agent_bio(get_the_ID());
                                    if ($bio) {
                                        echo wp_kses_post($bio);
                                    } else {
                                        the_content();
                                    }
                                } else {
                                    the_content();
                                }
                                ?>
                            </div>
                        </section>
                        
                        <!-- Specialties -->
                        <?php if (function_exists('hpt_get_agent_specialties')) : 
                            $specialties = hpt_get_agent_specialties(get_the_ID());
                            if ($specialties && is_array($specialties)) : ?>
                                <section class="agent-specialties section">
                                    <h2 class="section-title"><?php esc_html_e('Specialties', 'happy-place-theme'); ?></h2>
                                    <div class="specialties-list">
                                        <?php foreach ($specialties as $specialty) : ?>
                                            <span class="badge badge-primary mr-2 mb-2"><?php echo esc_html($specialty); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Current Listings -->
                        <?php if (function_exists('hpt_get_agent_active_listings')) : 
                            $active_listings = hpt_get_agent_active_listings(get_the_ID(), 6);
                            if ($active_listings) : ?>
                                <section class="agent-listings section">
                                    <h2 class="section-title"><?php esc_html_e('Current Listings', 'happy-place-theme'); ?></h2>
                                    <div class="listings-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                        <?php foreach ($active_listings as $listing_post) : 
                                            setup_postdata($listing_post); ?>
                                            <div class="listing-item">
                                                <?php get_template_part('template-parts/listing-card'); ?>
                                            </div>
                                        <?php endforeach; 
                                        wp_reset_postdata(); ?>
                                    </div>
                                    
                                    <?php if (count($active_listings) >= 6) : ?>
                                        <div class="text-center mt-6">
                                            <a href="<?php echo esc_url(add_query_arg('agent', get_the_ID(), get_post_type_archive_link('listing'))); ?>" class="btn btn-outline">
                                                <?php esc_html_e('View All Listings', 'happy-place-theme'); ?> →
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </section>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Recent Sales -->
                        <?php if (function_exists('hpt_get_agent_sold_listings')) : 
                            $sold_listings = hpt_get_agent_sold_listings(get_the_ID(), 4);
                            if ($sold_listings) : ?>
                                <section class="agent-sales section">
                                    <h2 class="section-title"><?php esc_html_e('Recent Sales', 'happy-place-theme'); ?></h2>
                                    <div class="sales-grid row">
                                        <?php foreach ($sold_listings as $sold_listing) : 
                                            setup_postdata($sold_listing); ?>
                                            <div class="col-md-6 mb-4">
                                                <div class="sale-item card">
                                                    <div class="row no-gutters">
                                                        <div class="col-4">
                                                            <?php if (has_post_thumbnail($sold_listing->ID)) : ?>
                                                                <?php echo get_the_post_thumbnail($sold_listing->ID, 'thumbnail', array('class' => 'img-responsive')); ?>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-8">
                                                            <div class="card-body">
                                                                <h5 class="card-title"><?php echo esc_html(get_the_title($sold_listing->ID)); ?></h5>
                                                                <?php if (function_exists('hpt_get_listing_price')) : ?>
                                                                    <p class="sale-price text-success font-bold">
                                                                        <?php esc_html_e('Sold for:', 'happy-place-theme'); ?> 
                                                                        <?php echo esc_html(hpt_get_listing_price($sold_listing->ID)); ?>
                                                                    </p>
                                                                <?php endif; ?>
                                                                <p class="sale-date text-muted">
                                                                    <?php echo esc_html(get_the_date('', $sold_listing->ID)); ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; 
                                        wp_reset_postdata(); ?>
                                    </div>
                                </section>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        
                        <!-- Agent Stats -->
                        <div class="agent-stats widget">
                            <h3 class="widget-title"><?php esc_html_e('Agent Statistics', 'happy-place-theme'); ?></h3>
                            <div class="stats-list">
                                
                                <?php if (function_exists('hpt_get_agent_years_experience')) : 
                                    $experience = hpt_get_agent_years_experience(get_the_ID());
                                    if ($experience) : ?>
                                        <div class="stat-item">
                                            <div class="stat-number"><?php echo esc_html($experience); ?></div>
                                            <div class="stat-label"><?php esc_html_e('Years Experience', 'happy-place-theme'); ?></div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if (function_exists('hpt_get_agent_total_sales')) : 
                                    $total_sales = hpt_get_agent_total_sales(get_the_ID());
                                    if ($total_sales) : ?>
                                        <div class="stat-item">
                                            <div class="stat-number"><?php echo esc_html(number_format($total_sales)); ?></div>
                                            <div class="stat-label"><?php esc_html_e('Properties Sold', 'happy-place-theme'); ?></div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if (function_exists('hpt_get_agent_active_listings_count')) : 
                                    $active_count = hpt_get_agent_active_listings_count(get_the_ID());
                                    if ($active_count) : ?>
                                        <div class="stat-item">
                                            <div class="stat-number"><?php echo esc_html($active_count); ?></div>
                                            <div class="stat-label"><?php esc_html_e('Active Listings', 'happy-place-theme'); ?></div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if (function_exists('hpt_get_agent_rating')) : 
                                    $rating = hpt_get_agent_rating(get_the_ID());
                                    if ($rating) : ?>
                                        <div class="stat-item">
                                            <div class="stat-number"><?php echo esc_html($rating); ?>/5</div>
                                            <div class="stat-label"><?php esc_html_e('Client Rating', 'happy-place-theme'); ?></div>
                                        </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Form -->
                        <div class="widget" style="margin-bottom: var(--hph-space-3xl);">
                            <h3 class="widget-title"><?php esc_html_e('Contact Me', 'happy-place-theme'); ?></h3>
                            <div class="card">
                                <div class="card-body">
                                    <form class="agent-contact-form" method="post">
                                        <div class="form-group" style="margin-bottom: var(--hph-space-lg);">
                                            <input type="text" name="contact_name" class="form-input" placeholder="<?php esc_attr_e('Your Name', 'happy-place-theme'); ?>" required>
                                        </div>
                                        <div class="form-group" style="margin-bottom: var(--hph-space-lg);">
                                            <input type="email" name="contact_email" class="form-input" placeholder="<?php esc_attr_e('Your Email', 'happy-place-theme'); ?>" required>
                                        </div>
                                        <div class="form-group" style="margin-bottom: var(--hph-space-lg);">
                                            <input type="tel" name="contact_phone" class="form-input" placeholder="<?php esc_attr_e('Your Phone', 'happy-place-theme'); ?>">
                                        </div>
                                        <div class="form-group" style="margin-bottom: var(--hph-space-xl);">
                                            <textarea name="contact_message" class="form-textarea" rows="4" placeholder="<?php esc_attr_e('Your Message', 'happy-place-theme'); ?>" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <?php esc_html_e('Send Message', 'happy-place-theme'); ?>
                                        </button>
                                        <input type="hidden" name="agent_id" value="<?php echo esc_attr(get_the_ID()); ?>">
                                        <?php wp_nonce_field('contact_agent_' . get_the_ID(), 'contact_agent_nonce'); ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Agent Info -->
                        <div class="agent-info widget">
                            <h3 class="widget-title"><?php esc_html_e('Agent Information', 'happy-place-theme'); ?></h3>
                            <div class="info-list">
                                
                                <?php if (function_exists('hpt_get_agent_license')) : 
                                    $license = hpt_get_agent_license(get_the_ID());
                                    if ($license) : ?>
                                        <div class="info-item">
                                            <strong><?php esc_html_e('License #:', 'happy-place-theme'); ?></strong>
                                            <?php echo esc_html($license); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if (function_exists('hpt_get_agent_languages')) : 
                                    $languages = hpt_get_agent_languages(get_the_ID());
                                    if ($languages && is_array($languages)) : ?>
                                        <div class="info-item">
                                            <strong><?php esc_html_e('Languages:', 'happy-place-theme'); ?></strong>
                                            <?php echo esc_html(implode(', ', $languages)); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if (function_exists('hpt_get_agent_certifications')) : 
                                    $certifications = hpt_get_agent_certifications(get_the_ID());
                                    if ($certifications && is_array($certifications)) : ?>
                                        <div class="info-item">
                                            <strong><?php esc_html_e('Certifications:', 'happy-place-theme'); ?></strong>
                                            <ul class="list-unstyled">
                                                <?php foreach ($certifications as $certification) : ?>
                                                    <li><?php echo esc_html($certification); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                        
                        <!-- Social Media -->
                        <?php if (function_exists('hpt_get_agent_social_links')) : 
                            $social_links = hpt_get_agent_social_links(get_the_ID());
                            if ($social_links && is_array($social_links)) : ?>
                                <div class="agent-social widget">
                                    <h3 class="widget-title"><?php esc_html_e('Follow Me', 'happy-place-theme'); ?></h3>
                                    <div class="social-links">
                                        <?php foreach ($social_links as $platform => $url) : 
                                            if ($url) : ?>
                                                <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" class="social-link <?php echo esc_attr($platform); ?>">
                                                    <i class="fab fa-<?php echo esc_attr($platform); ?>"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                    </div>
                    
                </div>
            </div>
            
        </div>
    </main>
    
<?php endwhile; ?>

<?php get_footer(); ?>
