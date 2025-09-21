<?php
/**
 * Template Name: Happy Place Home Page
 * 
 * Complete home page with multiple sections showcasing real estate services
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header();
?>

    <?php
    // ============================================
    // Hero Section - Welcome to Happy Place
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'gradient',
        'theme' => 'dark',
        'backdrop_blur' => false,
        'backdrop_blur_intensity' => 'sm', // sm, md, lg, xl
        'content_animation' => 'fade-up', // fade-up, slide-up, zoom-in, bounce-in, none
        'animation_delay' => 500, // Delay in milliseconds
        'animation_duration' => 800, // Duration in milliseconds
        'height' => 'full',
        'is_top_of_page' => true,
        'background_image' => get_template_directory_uri() . '/assets/images/home-hero.jpg',
        'ken_burns' => false,
        'ken_burns_direction' => 'zoom-pan',
        'ken_burns_duration' => 25,
        'overlay' => 'dark',
        'alignment' => 'left',
        'headline' => 'Find Your Happy Place',
        'subheadline' => "Whether you're buying, selling, or just browsing, we're here to help",
        'content_width' => 'normal',
        'buttons' => array(
            array(
                'text' => 'Start Your Search',
                'url' => '/listings/',
                'style' => 'white',
                'size' => 'm',
                'icon' => 'fas fa-search',
                'icon_position' => 'right'
            ),
            array(
                'text' => 'Meet Our Team',
                'url' => '/meet-the-team/',
                'style' => 'outline-white',
                'size' => 'm'
            )
        ),
        'section_id' => 'hero-home'
    ));


    // ============================================
    // Features Section with Images - Why Choose Us
    // ============================================
    /*
    get_template_part('template-parts/sections/features-with-images', null, array(
       'section_background' => 'dark',
        'headline' => 'When you combine local expertise with an industry leading approach, you get more than just a real estate company.',
        'subheadline' => '',
        'columns' => 3,
        'card_height' => 'tall',
        'overlay_type' => 'gradient',
        'overlay_color' => 'dark',
        'hover_effect' => 'zoom',
        'features' => array(
            array(
                'backround_image' => array(
                    'url' => hph_get_image_url('our-story.jpg'),
                    'alt' => 'Local area photo'
                ),
                'title' => 'The Local Expertise',
                ),
            array(
                'background_image' => array(
                    'url' => hph_add_fastly_optimization(hph_get_image_url_only('assets/images/parker-agents.jpg'), 'large'),
                    'alt' => 'Professional team meeting'
                ),
                'title' => 'The Integrated Approach',
            ),
            array(
                'background_image' => array(
                    'url' => hph_get_image_url_only('assets/images/connection.jpg'),
                    'alt' => 'Modern home interior'
                ),
                'title' => 'The Personal Touch',
            )
        ),
        'animation' => false,
        'section_id' => 'features-why-choose'
    ));
    ?>
    */


   get_template_part('template-parts/sections/content', null, array(
        'layout' => 'left-image',
        'background' => 'light',
        'padding' => 'xl',
        'alignment' => 'left',
        'image_size' => 'medium',
        'image_style' => 'square',
        'image' => array(
            'url' => function_exists('hph_get_image_url') ? hph_get_image_url('Our Story-11.jpg') : '',
            'alt' => '',
            'caption' => ''
        ),
        'headline' => 'A community first vision.',
        'subheadline' => 'Our goal was simple, but powerful:',
        'content' => "To create a future where finding your happy place is faster, easier, and more personalized than ever before. To improve the community and the lives of those who call it home. With a passion for exceptional service and innovative marketing, we're committed to transforming the real estate landscape and building stronger, more connected communities.",
        'buttons' => array(
            array(
                'text' => 'Our Story',
                'url' => '/about/',
                'style' => 'outline-primary',
                'icon' => 'fas fa-compass'
            )
        ),
        'animation' => true,
        'section_id' => 'content-left-image'
    ));
    ?>

    <?php
   get_template_part('template-parts/sections/content', null, array(
        'layout' => 'right-image',
        'background' => 'dark',
        'padding' => 'xl',
        'alignment' => 'left',
        'image_size' => 'medium',
        'image_style' => 'square',
        'image' => array(
            'url' => function_exists('hph_get_image_url') ? hph_get_image_url('team-fun.jpg') : '',
            'alt' => 'Modern home interior'
        ),
        'headline' => 'A Team Approach',
        'subheadline' => 'High fives, not high stress.',
        'content' => 'The Parker Group is different because we approach every transaction as a team. Our agents, marketing experts, transaction coordinators, and admin staff all work together to ensure that every client has a seamless and successful experience from start to finish. <br> This means you can focus on what matters most - finding your happy place.',
        'buttons' => array(
            array(
                'text' => 'See Listings',
                'url' => '/listings',
                'style' => 'primary',
                'icon' => 'fas fa-compass'
            ),
            array(
                'text' => 'Get Your Home Value',
                'url' => '#',
                'style' => 'outline',
                'data_attributes' => 'data-modal-form="fello-search-widget" data-modal-id="hph-fello-modal" data-modal-title="Get Your Home Value" data-modal-subtitle="Find out what your home is worth in today\'s market."'
            )
        ),
        'animation' => true,
        'section_id' => 'content-right-image'
    ));
    ?>

     <?php
    // ============================================
    // Stats Section - Selling Success
    // ============================================
    get_template_part('template-parts/sections/stats', null, array(
        'style' => 'minimal',
        'theme' => 'primary',
        'padding' => 'xl',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('selling-stats-bg.jpg') : '',
        'overlay' => 'dark',
        'badge' => 'Selling Results',
        'headline' => 'A proven track record.',
        'subheadline' => '',
        'stats' => array(
            array(
                'number' => '$922M',
                'label' => 'In Total Volume',
                'icon' => 'fas fa-dollar',
                'description' => ''
            ),
            array(
                'number' => '50+',
                'label' => 'Skilled Agents',
                'icon' => 'fas fa-users',
                'description' => ''
            ),
            array(
                'number' => '2,800+',
                'label' => 'Homes Sold',
                'icon' => 'fas fa-home',
                'description' => ''
            ),
            array(
                'number' => '51%',
                'label' => 'Faster Sales Avg.',
                'icon' => 'fas fa-clock',
                'description' => ''
            )
        ),
        'animate_counters' => true,
        'section_id' => 'stats-sellers'
    ));
    ?>

    <?php
    // ============================================
    // New Listings Grid Section
    // ============================================
    get_template_part('template-parts/sections/universal-loop', null, array(
        'post_type' => 'listing',
        'layout' => 'grid',
        'new_listings_only' => true,
        'property_status' => 'active',
        'posts_per_page' => 6,
        'orderby' => 'date',
        'order' => 'DESC',
        'columns' => 3,

        // Enhanced styling to match other home page sections
        'background' => 'white',
        'padding' => '2xl',
        'card_variant' => 'elevated',
        'card_size' => 'lg',

        // Professional section styling
        'badge' => 'Just Listed',
        'title' => 'Fresh on the Market',
        'subtitle' => 'Brand new properties just listed and ready for you to explore',
        'show_empty_state' => false,

        'section_id' => 'new-listings-showcase'
    ));
    ?>

    <?php
    // ============================================
    // CTA Section - Get Started Today
    // ============================================
    get_template_part('template-parts/sections/cta', null, array(
        'layout' => 'centered',
        'background' => 'gradient',
        'padding' => '2xl',
        'badge' => 'Ready to Get Started?',
        'headline' => 'Your Dream Home Awaits',
        'subheadline' => 'Join thousands of satisfied clients who found their happy place with us',
        'content' => 'Contact us today for a free consultation and let our expert team help you navigate the real estate market with confidence.',
        'buttons' => array(
            array(
                'text' => 'Contact Us Today',
                'url' => '#',
                'data_attributes' => 'data-modal-form="general-contact" data-modal-title="Contact Us Today" data-modal-subtitle="Let our expert team help you navigate the real estate market with confidence."',
                'style' => 'white',
                'size' => 'm',
                'icon' => 'fas fa-phone'
            ),
            array(
                'text' => 'Browse Properties',
                'url' => '/listings/',
                'style' => 'outline-white',
                'size' => 'm',
                'icon' => 'fas fa-search'
            )
        ),
        'animation' => true,
        'section_id' => 'cta-home'
    ));
    ?>

<?php get_footer(); ?>
