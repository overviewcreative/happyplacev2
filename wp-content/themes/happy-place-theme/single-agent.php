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
        <?php 
        $cover_photo = get_field('cover_photo', get_the_ID());
        if ($cover_photo) : ?>
            <div class="hero-image">
                <img src="<?php echo esc_url(is_array($cover_photo) ? $cover_photo['url'] : $cover_photo); ?>" alt="<?php the_title(); ?>" class="img-responsive w-full h-96 object-cover">
            </div>
        <?php endif; ?>
        
        <div class="hero-overlay">
            <div class="container">
                <div class="hero-content text-white">
                    <div class="agent-intro">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php 
                                $agent_photo_displayed = false;
                                
                                // Try featured image first
                                if (has_post_thumbnail()) {
                                    the_post_thumbnail('medium', array('class' => 'agent-avatar rounded-circle'));
                                    $agent_photo_displayed = true;
                                } else {
                                    // Try ACF photo fields
                                    $profile_photo = get_field('agent_photo', get_the_ID()) ?: get_field('photo', get_the_ID()) ?: get_field('profile_image', get_the_ID());
                                    if ($profile_photo) {
                                        $photo_url = is_array($profile_photo) ? $profile_photo['url'] : $profile_photo;
                                        if ($photo_url) : ?>
                                            <img src="<?php echo esc_url($photo_url); ?>" alt="<?php the_title(); ?>" class="agent-avatar rounded-circle">
                                            <?php $agent_photo_displayed = true; ?>
                                        <?php endif;
                                    }
                                }
                                
                                // Fallback placeholder if no photo found
                                if (!$agent_photo_displayed) : ?>
                                    <div class="agent-avatar-placeholder rounded-circle">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-10">
                                <h1 class="agent-name text-4xl font-bold mb-2"><?php the_title(); ?></h1>
                                
                                <?php 
                                $agent_title = get_field('agent_title', get_the_ID()) ?: get_field('title', get_the_ID());
                                if ($agent_title) : ?>
                                    <p class="agent-title text-xl mb-3"><?php echo esc_html($agent_title); ?></p>
                                <?php endif; ?>
                                
                                <div class="agent-contact">
                                    <?php 
                                    // Phone with multiple field fallbacks
                                    $phone = function_exists('hpt_get_agent_phone') ? hpt_get_agent_phone(get_the_ID()) : null;
                                    if (!$phone && function_exists('get_field')) {
                                        $phone = get_field('phone', get_the_ID()) ?: get_field('agent_phone', get_the_ID()) ?: get_field('contact_phone', get_the_ID()) ?: get_field('mobile', get_the_ID());
                                    }
                                    ?>
                                    
                                    <?php if ($phone) : ?>
                                        <a href="tel:<?php echo esc_attr($phone); ?>" class="btn btn-primary mr-3">
                                            <i class="fas fa-phone mr-2"></i>
                                            <?php echo esc_html($phone); ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="btn btn-secondary mr-3 disabled">
                                            <i class="fas fa-phone mr-2"></i>
                                            Phone Available Upon Request
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    // Email with multiple field fallbacks
                                    $email = function_exists('hpt_get_agent_email') ? hpt_get_agent_email(get_the_ID()) : null;
                                    if (!$email && function_exists('get_field')) {
                                        $email = get_field('email', get_the_ID()) ?: get_field('agent_email', get_the_ID()) ?: get_field('contact_email', get_the_ID());
                                    }
                                    // Fallback to user email if agent has associated user
                                    if (!$email) {
                                        $user_id = get_field('user_id', get_the_ID());
                                        if ($user_id) {
                                            $user = get_userdata($user_id);
                                            if ($user) {
                                                $email = $user->user_email;
                                            }
                                        }
                                    }
                                    ?>
                                    
                                    <?php if ($email) : ?>
                                        <a href="mailto:<?php echo esc_attr($email); ?>" class="btn btn-outline btn-light">
                                            <i class="fas fa-envelope mr-2"></i>
                                            <?php esc_html_e('Email Me', 'happy-place-theme'); ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="btn btn-outline btn-secondary disabled">
                                            <i class="fas fa-envelope mr-2"></i>
                                            Contact Available Upon Request
                                        </span>
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
                                // Try multiple bio field sources
                                $bio = get_field('bio', get_the_ID()) ?: get_field('agent_bio', get_the_ID()) ?: get_field('description', get_the_ID()) ?: get_field('about', get_the_ID());
                                
                                // Use post content if no ACF bio
                                if (!$bio) {
                                    $bio = get_the_content();
                                }
                                
                                // Final fallback if still no content
                                if (!$bio || trim(strip_tags($bio)) === '') {
                                    $agent_name = get_the_title();
                                    $bio = "Welcome! I'm " . esc_html($agent_name) . ", and I'm here to help you with all your real estate needs. Whether you're buying, selling, or just exploring your options, I'm committed to providing you with exceptional service and expertise. Please don't hesitate to reach out - I'd love to help you achieve your real estate goals.";
                                }
                                
                                echo wp_kses_post($bio);
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
                        <?php 
                        $agent_id = get_the_ID();
                        $active_listings = get_posts(array(
                            'post_type' => 'listing',
                            'posts_per_page' => 6,
                            'meta_query' => array(
                                array(
                                    'key' => 'listing_agent',
                                    'value' => $agent_id,
                                    'compare' => '='
                                ),
                                array(
                                    'key' => 'listing_status',
                                    'value' => 'active',
                                    'compare' => '='
                                )
                            )
                        ));
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
                                
                                <!-- Experience Years - Always show with fallback -->
                                <div class="stat-item">
                                    <div class="stat-number">
                                        <?php 
                                        $experience = get_field('experience_years', get_the_ID()) ?: get_field('years_experience', get_the_ID()) ?: get_field('experience', get_the_ID());
                                        if ($experience && is_numeric($experience)) {
                                            echo esc_html($experience);
                                        } else {
                                            echo '5+'; // Default professional experience
                                        }
                                        ?>
                                    </div>
                                    <div class="stat-label"><?php esc_html_e('Years Experience', 'happy-place-theme'); ?></div>
                                </div>
                                
                                <?php if (function_exists('hpt_get_agent_total_sales')) : 
                                    $total_sales = hpt_get_agent_total_sales(get_the_ID());
                                    if ($total_sales) : ?>
                                        <div class="stat-item">
                                            <div class="stat-number"><?php echo esc_html(number_format($total_sales)); ?></div>
                                            <div class="stat-label"><?php esc_html_e('Properties Sold', 'happy-place-theme'); ?></div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php 
                                $active_count_query = new WP_Query(array(
                                    'post_type' => 'listing',
                                    'posts_per_page' => -1,
                                    'meta_query' => array(
                                        array(
                                            'key' => 'listing_agent',
                                            'value' => get_the_ID(),
                                            'compare' => '='
                                        ),
                                        array(
                                            'key' => 'listing_status',
                                            'value' => 'active',
                                            'compare' => '='
                                        )
                                    )
                                ));
                                $active_count = $active_count_query->found_posts;
                                if ($active_count) : ?>
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo esc_html($active_count); ?></div>
                                        <div class="stat-label"><?php esc_html_e('Active Listings', 'happy-place-theme'); ?></div>
                                    </div>
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
