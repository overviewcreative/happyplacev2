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
        'theme' => 'primary',
        'height' => 'lg',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('home-hero.jpg') : '',
        'parallax' => true,
        'overlay' => 'gradient',
        'alignment' => 'left',
        'headline' => 'Find Your Happy Place',
        'subheadline' => "Whether you're buying, selling, or just browsing, we're here to help",
        'content' => 'Discover exceptional properties and expert guidance to make your real estate journey seamless and successful.',
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
                'url' => '/agents/',
                'style' => 'outline-white',
                'size' => 'm'
            )
        ),
        'section_id' => 'hero-home'
    ));
    ?>

    <?php
    // ============================================
    // Features Section with Images - Why Choose Us
    // ============================================
    get_template_part('template-parts/sections/features-with-images', null, array(
        'layout' => 'cards',
        'background' => 'dark',
        'alignment' => 'center',
        'padding' => 'xl',
        'columns' => 3,
        'badge' => 'The Parker Group',
        'headline' => 'When you combine local expertise with an industry leading approach, you get more than just a real estate company.',
        'subheadline' => 'You get a group of trusted advisors here for you every step of the way.',
        'image_style' => 'square',
        'image_position' => 'top',
        'animation' => 'true',
        'features' => array(
            array(
                'image' => array(
                    'url' => get_template_directory_uri() . '/assets/images/live-local.jpg',
                    'alt' => 'Local area photo'
                ),
                'title' => 'The Local Expertise',
                'content' => "With deep roots in Delmarva, we understand the unique qualities of our communities and are dedicated to helping you navigate the market whether you're looking to buy or sell."
            ),
            array(
                'image' => array(
                    'url' => get_template_directory_uri() . '/assets/images/parker-agents.jpg',
                    'alt' => 'Professional team meeting'
                ),
                'title' => 'The Integrated Approach',
                'content' => "We don/t believe in following the established path just because it's there--our approach is to think outside of the box, leverage new technology, and continue evolving to best serve our clients."
            ),
            array(
                'image' => array(
                    'url' => get_template_directory_uri() . '/assets/images/connection.jpg',
                    'alt' => 'Modern home interior'
                ),
                'title' => 'The Personal Touch',
                'content' => 'Our local knowledge mixed with our innovative approach is the foundation for one of the most valuable assets in real estate - connections. Our Agents are constantly leveling up their skills and partnerships to best serve their clients.'
            )
        ),
        'animation' => false,
        'section_id' => 'features-why-choose'
    ));
    ?>

   <?php
   get_template_part('template-parts/sections/content', null, array(
        'layout' => 'left-image',
        'background' => 'light',
        'padding' => 'xl',
        'alignment' => 'left',
        'image_style' => 'default',
        'image_size' => 'medium',
        'image' => array(
            'url' => function_exists('hph_get_image_url') ? hph_get_image_url('dustin-rachel.jpg') : '',
            'alt' => 'Dustin & Rachel Parker of The Parker Group',
            'image_style' => 'default',
            'caption' => 'Dustin & Rachel Parker'
        ),
        'headline' => 'A community first vision.',
        'subheadline' => 'Our goal was simple, but powerful:',
        'content' => "To create a future where finding your happy place is faster, easier, and more personalized than ever before. To improve the community and the lives of those who call it home. With a passion for exceptional service and innovative marketing, we're committed to transforming the real estate landscape and building stronger, more connected communities.",
        'buttons' => array(
            array(
                'text' => 'Our Story',
                'url' => '/about/',
                'style' => 'primary',
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
            'alt' => 'Modern home interior',
            'image_style' => 'square'
        ),
        'headline' => 'A Team Approach',
        'subheadline' => 'We believe that buying or selling a home should feel good.',
        'content' => 'Combine compelling visuals with your message. This layout places the image on the left, creating a natural reading flow from visual to text.',
        'buttons' => array(
            array(
                'text' => 'See Listings',
                'url' => '#',
                'style' => 'primary',
                'icon' => 'fas fa-compass'
            ),
            array(
                'text' => 'Get Your Home Value',
                'url' => '#',
                'style' => 'outline'
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
                'number' => '102%',
                'label' => 'Average Sale Price',
                'icon' => 'fas fa-percentage',
                'description' => 'Of original listing price achieved'
            ),
            array(
                'number' => '50+',
                'label' => 'Skilled Agents',
                'icon' => 'fas fa-users',
                'description' => 'Average time to sell vs 45 day market average'
            ),
            array(
                'number' => '2,800+',
                'label' => 'Homes Sold',
                'icon' => 'fas fa-home',
                'description' => 'and counting.'
            ),
            array(
                'number' => '95%',
                'label' => 'Client Referral Rate',
                'icon' => 'fas fa-thumbs-up',
                'description' => 'Sellers who recommend us to others'
            )
        ),
        'animate_counters' => true,
        'section_id' => 'stats-sellers'
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
