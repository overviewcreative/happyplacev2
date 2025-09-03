<?php
/**
 * Template Name: Complete Section Showcase
 * 
 * Comprehensive showcase of all section variations (Hero, Content, CTA, Features)
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header();
?>

    <?php
    // ============================================
    // Hero with Rotating Headline
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'gradient', // 'gradient' 'image' 'solid' 'property' 'minimal'
        'theme' => 'primary', // 'primary' 'secondary' 'accent' 'ocean' 'sunset' 'forest' 'success' 'info' 'dark' 'light'
        'height' => 'lg', // 'sm' 'md' 'lg' 'xl' 'full'
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 10.jpg') : '',
        'parallax' => true, // true false
        'overlay' => 'gradient', // 'none' 'dark' 'dark-subtle' 'dark-heavy' 'light' 'light-subtle' 'gradient' 'gradient-reverse' 'gradient-radial' 'primary' 'hero' 'scrim-top' 'scrim-bottom' 'diagonal' 'custom'
        'alignment' => 'left', // 'left' 'center' 'right'
        // Rotating headline configuration
        'headline' => '', // Leave empty when using rotation
        'headline_prefix' => 'Home is where the',
        'headline_suffix' => '',
        'rotating_words' => array(
            'dogs sleep',
            'barbeques happen', 
            'messes are made',
            'game nights are',
            'milk is spilled'
        ),
        'rotation_type' => 'slide', // 'typing' 'fade' 'slide' 'flip'
        'rotation_speed' => 3000, // Time between rotations in milliseconds (e.g., 2000, 3000, 5000)
        'typing_speed' => 100, // Speed of typing effect in milliseconds per character (e.g., 50, 100, 150)
        'subheadline' => "Whether you're buying, selling, or just browsing, we're here to help",
        'content' => 'Discover exceptional properties and expert guidance to make your real estate journey seamless and successful.',
        'content_width' => 'normal', // 'narrow' 'normal' 'wide' 'full'
        'fade_in' => false, // true false
        'scroll_indicator' => false, // true false
        'buttons' => array(
            array(
                'text' => 'Start Your Search',
                'url' => '#',
                'style' => 'white', // 'white' 'outline-white' 'primary' 'outline-primary' 'secondary' 'outline-secondary'
                'size' => 'l', // 's' 'm' 'l' 'xl'
                'icon' => 'fas fa-search', // Any Font Awesome icon class
                'icon_position' => 'right', // 'left' 'right'
                'target' => '_self' // '_self' '_blank' '_parent' '_top'
            ),
            array(
                'text' => 'View Listings',
                'url' => '#',
                'style' => 'outline-white', // 'white' 'outline-white' 'primary' 'outline-primary' 'secondary' 'outline-secondary'
                'size' => 'l', // 's' 'm' 'l' 'xl'
                'target' => '_self' // '_self' '_blank' '_parent' '_top'
            )
        ),
        'section_id' => 'hero-rotating'
    ));
    ?>

    

<style>
    /* Add smooth scrolling for navigation */
    html {
        scroll-behavior: smooth;
    }
    
    /* Add some spacing between hero sections for demo purposes */
    .hero-variations-page .hph-hero {
        margin-bottom: 2px;
    }
    
    /* Highlight sections on hover for demo */
    .hero-variations-page .hph-hero {
        transition: transform 0.3s ease;
    }
    
    /* Make navigation sticky and styled */
    @media (max-width: 768px) {
        /* Hide navigation on mobile */
        div[style*="position: fixed"] {
            display: none;
        }
    }
</style>

<?php get_footer(); ?>