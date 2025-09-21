<?php
/**
 * Single Agent Template - Modern Section-Based Design
 * 
 * Complete agent profile using the unified section system:
 * - Hero section with agent photo and key info
 * - Stats section showcasing performance metrics
 * - Features section highlighting agent services
 * - Contact form section for lead generation
 * - Agent's current listings showcase
 * - Testimonials from satisfied clients
 * 
 * @package HappyPlaceTheme
 * @since 3.2.0
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get agent ID and verify it exists
$agent_id = get_the_ID();
if (!$agent_id || get_post_type($agent_id) !== 'agent') {
    get_template_part('template-parts/base/content-none');
    get_footer();
    return;
}

// Get agent data
$first_name = get_field('first_name', $agent_id);
$last_name = get_field('last_name', $agent_id);
$title = get_field('title', $agent_id);
$email = get_field('email', $agent_id);
$phone = get_field('phone', $agent_id);
$bio = get_field('bio', $agent_id);
$years_experience = get_field('years_experience', $agent_id);
$specialties = get_field('specialties', $agent_id);
$languages = get_field('languages', $agent_id);
$license_number = get_field('license_number', $agent_id);
$total_sales_volume = get_field('total_sales_volume', $agent_id);
$total_listings_sold = get_field('total_listings_sold', $agent_id);
$featured = get_field('featured', $agent_id);

// Office information
$office_id = get_field('office', $agent_id);
$office_name = '';
$office_address = '';
if ($office_id) {
    $office_name = get_the_title($office_id);
    $office_address = get_field('address', $office_id);
}

// Social media
$facebook = get_field('facebook', $agent_id);
$instagram = get_field('instagram', $agent_id);
$linkedin = get_field('linkedin', $agent_id);
$twitter = get_field('twitter', $agent_id);

// Build full name
$full_name = trim($first_name . ' ' . $last_name);
if (empty($full_name)) {
    $full_name = get_the_title($agent_id);
}

// Get agent photo - use profile photo field instead of featured image
$agent_photo = get_field('profile_photo', $agent_id);
if ($agent_photo && is_array($agent_photo)) {
    // ACF image field returns array
    $agent_photo = $agent_photo['sizes']['large'] ?? $agent_photo['url'];
} elseif ($agent_photo && is_numeric($agent_photo)) {
    // If it's an attachment ID
    $agent_photo = wp_get_attachment_image_url($agent_photo, 'large');
} elseif (!$agent_photo) {
    // Fallback to featured image if profile photo not set
    $agent_photo = get_the_post_thumbnail_url($agent_id, 'large');
}

// Final fallback to placeholder
if (!$agent_photo) {
    $agent_photo = get_template_directory_uri() . '/assets/images/placeholder-agent.jpg';
}
?>

    <?php
    // ============================================
    // Hero Section - Agent Profile
    // ============================================
    $bio_preview = $bio ? wp_trim_words(strip_tags($bio), 25, '...') : 'Experienced real estate professional dedicated to helping clients achieve their property goals.';
    ?>

    <!-- Custom Hero Section with Agent Profile Photo -->
    <section 
        class="hph-hero-section"
        id="hero-agent-<?php echo esc_attr($agent_id); ?>"
        data-bg="gradient"
        style="position: relative; width: 100%; background: var(--hph-gradient-primary); color: var(--hph-white); padding-top: var(--hph-padding-3xl); padding-bottom: var(--hph-padding-3xl);"
        data-animation="false"
    >
        <div style="position: relative; z-index: 10; margin-left: auto; margin-right: auto; padding-left: var(--hph-padding-lg); padding-right: var(--hph-padding-lg); max-width: var(--hph-container-xl);">
            
            <!-- Hero Content -->
            <div style="text-align: center;">
                
                <!-- Agent Profile Photo -->
                <div style="margin-bottom: var(--hph-margin-2xl);">
                    <div style="display: inline-block; position: relative;">
                        <div style="width: 200px; height: 200px; border-radius: var(--hph-radius-full); overflow: hidden; border: 6px solid rgba(255, 255, 255, 0.2); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2); margin: 0 auto;">
                            <img 
                                src="<?php echo esc_url($agent_photo); ?>" 
                                alt="<?php echo esc_attr($full_name); ?>" 
                                style="width: 100%; height: 100%; object-fit: cover;"
                                loading="eager"
                            >
                        </div>
                        
                        <?php if ($featured): ?>
                        <!-- Featured Badge -->
                        <div style="position: absolute; top: -10px; right: -10px; background: var(--hph-accent); color: var(--hph-white); border-radius: var(--hph-radius-full); padding: var(--hph-padding-sm) var(--hph-padding-md); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                            <i class="fas fa-star" style="margin-right: var(--hph-margin-xs);"></i>
                            <span><?php _e('Featured', 'happy-place-theme'); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Agent Name -->
                <h1 style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-5xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                    <?php echo esc_html($full_name); ?>
                </h1>
                
                <!-- Agent Title -->
                <?php if ($title): ?>
                <p style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); opacity: 0.9;">
                    <?php echo esc_html($title); ?>
                </p>
                <?php endif; ?>
                
                <!-- Office Info -->
                <?php if ($office_name): ?>
                <p style="margin: 0 0 var(--hph-margin-xl) 0; font-size: var(--hph-text-base); opacity: 0.8;">
                    <i class="fas fa-building" style="margin-right: var(--hph-margin-sm);"></i>
                    <?php echo esc_html($office_name); ?>
                </p>
                <?php endif; ?>
                
                <!-- Bio Preview -->
                <div style="font-size: var(--hph-text-base); line-height: var(--hph-leading-relaxed); max-width: 65ch; margin: 0 auto var(--hph-margin-2xl) auto; opacity: 0.85;">
                    <?php echo esc_html($bio_preview); ?>
                </div>
                
                <!-- Action Buttons -->
                <div style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-lg); align-items: center; justify-content: center;">
                    <?php if ($phone): ?>
                    <a 
                        href="tel:<?php echo esc_attr($phone); ?>"
                        style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none; font-weight: var(--hph-font-semibold); border-radius: var(--hph-radius-lg); transition: all 300ms ease; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); padding: var(--hph-padding-lg) var(--hph-padding-2xl); font-size: var(--hph-text-lg); background-color: var(--hph-white); color: var(--hph-primary); border: 2px solid var(--hph-white);"
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='translateY(0)'"
                    >
                        <i class="fas fa-phone" style="margin-right: var(--hph-margin-sm);"></i>
                        <span>Call <?php echo esc_html($phone); ?></span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($email): ?>
                    <a 
                        href="mailto:<?php echo esc_attr($email); ?>"
                        style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none; font-weight: var(--hph-font-semibold); border-radius: var(--hph-radius-lg); transition: all 300ms ease; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); padding: var(--hph-padding-lg) var(--hph-padding-2xl); font-size: var(--hph-text-lg); background-color: transparent; color: var(--hph-white); border: 2px solid var(--hph-white);"
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='translateY(0)'"
                    >
                        <i class="fas fa-envelope" style="margin-right: var(--hph-margin-sm);"></i>
                        <span>Send Email</span>
                    </a>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
    </section>
    <?php
    // ============================================
    // Stats Section - Agent Performance
    // ============================================
    $agent_stats = array_filter(array(
        $years_experience ? array(
            'number' => $years_experience,
            'label' => 'Years Experience',
            'icon' => 'fas fa-calendar',
            'description' => 'In real estate industry'
        ) : null,
        $total_listings_sold ? array(
            'number' => number_format($total_listings_sold),
            'label' => 'Properties Sold',
            'icon' => 'fas fa-home',
            'description' => 'Successfully closed transactions'
        ) : null,
        $total_sales_volume ? array(
            'number' => '$' . number_format($total_sales_volume / 1000000, 1) . 'M',
            'label' => 'Total Sales Volume',
            'icon' => 'fas fa-chart-line',
            'description' => 'In real estate sales'
        ) : null,
        array(
            'number' => '98%',
            'label' => 'Client Satisfaction',
            'icon' => 'fas fa-star',
            'description' => 'Happy clients who recommend'
        )
    ));

    if (!empty($agent_stats)) {
        get_template_part('template-parts/sections/stats', null, array(
            'style' => 'counters',
            'theme' => 'primary',
            'padding' => 'xl',
            'badge' => 'Performance Record',
            'headline' => $full_name . '\'s Track Record',
            'subheadline' => 'Proven results from an experienced professional',
            'stats' => $agent_stats,
            'animate_counters' => true,
            'section_id' => 'stats-agent-' . $agent_id
        ));
    }
    ?>

    <?php
    // ============================================
    // Content Section - About the Agent
    // ============================================
    if ($bio || $specialties || $languages) {
        get_template_part('template-parts/sections/content', null, array(
            'background' => 'light',
            'padding' => 'xl',
            'content_width' => 'normal',
            'badge' => 'About ' . ($first_name ?: $full_name),
            'headline' => 'Get to Know Your Agent',
            'subheadline' => 'Experience, expertise, and dedication to your success',
            'content' => $bio ? wp_kses_post(wpautop($bio)) : '',
            'layout' => 'single-column',
            'section_id' => 'about-agent-' . $agent_id
        ));
    }
    ?>
    <?php
    // ============================================
    // Features Section - Agent Services
    // ============================================
    $agent_services = array(
        array(
            'icon' => 'fas fa-search',
            'title' => 'Property Search',
            'content' => 'Expert guidance in finding properties that match your specific needs and budget requirements.'
        ),
        array(
            'icon' => 'fas fa-chart-line',
            'title' => 'Market Analysis',
            'content' => 'Detailed market reports and comparative analysis to help you make informed decisions.'
        ),
        array(
            'icon' => 'fas fa-handshake',
            'title' => 'Negotiation',
            'content' => 'Skilled negotiation to ensure you get the best possible terms and pricing.'
        ),
        array(
            'icon' => 'fas fa-camera',
            'title' => 'Professional Marketing',
            'content' => 'High-quality photography and comprehensive marketing to showcase your property.'
        ),
        array(
            'icon' => 'fas fa-users',
            'title' => 'Client Support',
            'content' => 'Dedicated support throughout the entire buying or selling process.'
        ),
        array(
            'icon' => 'fas fa-clipboard-check',
            'title' => 'Transaction Management',
            'content' => 'Handle all paperwork and coordinate with lenders, inspectors, and other professionals.'
        )
    );

    get_template_part('template-parts/sections/features', null, array(
        'layout' => 'grid',
        'background' => 'white',
        'padding' => 'xl',
        'columns' => 3,
        'badge' => 'Services',
        'headline' => 'How I Can Help You',
        'subheadline' => 'Comprehensive real estate services tailored to your needs',
        'content' => 'From initial consultation to closing day, I provide expert guidance and personalized service throughout your real estate journey.',
        'features' => $agent_services,
        'icon_style' => 'circle',
        'animation' => true,
        'section_id' => 'services-agent-' . $agent_id
    ));
    ?>

    <?php
    // ============================================
    // Listings Section - Agent's Properties
    // ============================================
    // Query agent's listings
    $listings_args = array(
        'post_type' => 'listing',
        'posts_per_page' => 6,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'agent',
                'value' => $agent_id,
                'compare' => '='
            )
        ),
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    $listings_query = new WP_Query($listings_args);
    $has_listings = $listings_query->have_posts();
    wp_reset_postdata();

    if ($has_listings) {
        get_template_part('template-parts/sections/listings-loop', null, array(
            'background' => 'light',
            'padding' => 'xl',
            'badge' => 'Current Listings',
            'headline' => $full_name . '\'s Properties',
            'subheadline' => 'Explore available properties from this agent',
            'posts_per_page' => 6,
            'agent_filter' => $agent_id,
            'show_agent' => false,
            'view_all_url' => add_query_arg('agent', $agent_id, get_post_type_archive_link('listing')),
            'section_id' => 'listings-agent-' . $agent_id
        ));
    }
    ?>
    <?php
    // ============================================
    // Contact Form Section
    // ============================================
    get_template_part('template-parts/sections/form', null, array(
        'background' => 'primary',
        'padding' => 'xl',
        'badge' => 'Get In Touch',
        'headline' => 'Contact ' . $full_name,
        'subheadline' => 'Ready to start your real estate journey? Let\'s connect today.',
        'content' => 'Whether you\'re buying, selling, or have questions about the market, I\'m here to help with personalized service and expert guidance.',
        'form_fields' => array(
            array(
                'type' => 'text',
                'name' => 'name',
                'placeholder' => 'Your Full Name',
                'required' => true
            ),
            array(
                'type' => 'email',
                'name' => 'email',
                'placeholder' => 'Your Email Address',
                'required' => true
            ),
            array(
                'type' => 'tel',
                'name' => 'phone',
                'placeholder' => 'Your Phone Number',
                'required' => false
            ),
            array(
                'type' => 'select',
                'name' => 'interest',
                'placeholder' => 'I\'m interested in...',
                'options' => array(
                    'buying' => 'Buying a Home',
                    'selling' => 'Selling a Home',
                    'investing' => 'Real Estate Investment',
                    'consultation' => 'Market Consultation',
                    'other' => 'Other'
                )
            ),
            array(
                'type' => 'textarea',
                'name' => 'message',
                'placeholder' => 'Tell me more about your needs...',
                'required' => true,
                'rows' => 4
            )
        ),
        'button_text' => 'Send Message',
        'contact_info' => array_filter(array(
            $phone ? array('type' => 'phone', 'value' => $phone, 'icon' => 'fas fa-phone') : null,
            $email ? array('type' => 'email', 'value' => $email, 'icon' => 'fas fa-envelope') : null,
            $office_address ? array('type' => 'address', 'value' => $office_address, 'icon' => 'fas fa-map-marker-alt') : null
        )),
        'social_links' => array_filter(array(
            $facebook ? array('url' => $facebook, 'icon' => 'fab fa-facebook', 'label' => 'Facebook') : null,
            $instagram ? array('url' => $instagram, 'icon' => 'fab fa-instagram', 'label' => 'Instagram') : null,
            $linkedin ? array('url' => $linkedin, 'icon' => 'fab fa-linkedin', 'label' => 'LinkedIn') : null,
            $twitter ? array('url' => $twitter, 'icon' => 'fab fa-twitter', 'label' => 'Twitter') : null
        )),
        'section_id' => 'contact-agent-' . $agent_id
    ));
    ?>

    <?php
    // ============================================
    // Testimonials Section - Client Reviews
    // ============================================
    get_template_part('template-parts/sections/testimonials', null, array(
        'background' => 'light',
        'padding' => 'xl',
        'badge' => 'Client Reviews',
        'headline' => 'What Clients Say About ' . $full_name,
        'subheadline' => 'Real feedback from satisfied clients',
        'testimonials_count' => 3,
        'layout' => 'slider',
        'show_ratings' => true,
        'auto_play' => true,
        'agent_filter' => $agent_id,
        'section_id' => 'testimonials-agent-' . $agent_id
    ));
    ?>


<?php get_footer(); ?>
