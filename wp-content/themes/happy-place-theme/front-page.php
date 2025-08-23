<?php
/**
 * Modern Front Page Template
 * 
 * Clean, modular homepage using the HPH content section system
 *
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header();

// Load our section helper functions
require_once get_template_directory() . '/template-parts/sections/section-helper.php';

// Hero Section - Main Hero with Image Background and Gradient Overlay
get_template_part('template-parts/sections/hero', null, array(
    'style' => 'image',
    'height' => 'lg',
    'background_image' => get_template_directory_uri() . '/assets/images/hero.jpg',
    'overlay' => 'gradient',
    'overlay_opacity' => '80',
    'alignment' => 'left',
    'content_width' => 'normal',
    'badge' => 'The Parker Group',
    'headline' => 'Find your happy place.',
    'subheadline' => 'Nobody ever said buying a home was simple, but that doesn\'t mean it has to be hard.',
    'content' => 'We are reimagining real estate by bringing a locally-focused, tech-enabled approach to every transaction, supported by our unique agent model.',
    'buttons' => array(
        array(
            'text' => 'Learn More',
            'url' => '/about/',
            'style' => 'white',
            'size' => 'l',
            'icon' => 'fas fa-home'
        ),
        array(
            'text' => 'See Listings',
            'url' => '/services/',
            'style' => 'outline-white',
            'size' => 'l',
            'icon' => 'fas fa-arrow-right'
        )
    ),
    'scroll_indicator' => false,
    'section_id' => 'hero'
));

// About Section
get_template_part('template-parts/sections/content', null, array(
    'layout' => 'right-image',
    'background' => 'white',
    'padding' => 'xl',
    'image' => array(
        'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 8.jpg',
        'alt' => 'Beautiful Delaware home interior',
        'width' => 600,
        'height' => 400
    ),
    'badge' => 'About Us',
    'headline' => 'Your Neighbors, Your Advocates, Your Guides',
    'subheadline' => 'More Than Just Real Estate Professionals',
    'content' => 'Our team was founded with one simple mission: to serve our community by helping others find their happy place. We bring personalized attention, honest communication, and a genuine commitment to your success to every transaction. With years of experience helping Delaware communities, we turn the complex process of buying a home into a journey you\'ll actually enjoy.',
    'buttons' => array(
        array(
            'text' => 'Meet Our Team',
            'url' => '/about/',
            'style' => 'primary',
            'size' => 'md',
            'icon' => 'fas fa-users'
        )
    ),
    'section_id' => 'about'
));

// Services Section
get_template_part('template-parts/sections/content', null, array(
    'layout' => 'left-image',
    'background' => 'light',
    'padding' => 'xl',
    'image' => array(
        'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 25.jpg',
        'alt' => 'Beautiful Delaware home living space',
        'width' => 600,
        'height' => 400
    ),
    'badge' => 'Our Services',
    'headline' => 'We\'re With You, Every Step of the Way',
    'subheadline' => 'Turning Complex Into Enjoyable',
    'content' => 'From your first property tour to the moment we hand you the keys, we\'re here to answer questions, solve problems, and celebrate victories both big and small. Every step outlined in our process is designed with your peace of mind at heart.',
    'buttons' => array(
        array(
            'text' => 'View Our Process',
            'url' => '/services/',
            'style' => 'primary',
            'size' => 'md',
            'icon' => 'fas fa-arrow-right'
        ),
        array(
            'text' => 'Schedule Consultation',
            'url' => '/contact/',
            'style' => 'outline',
            'size' => 'md',
            'icon' => 'fas fa-calendar'
        )
    ),
    'section_id' => 'services'
));

// CTA Section
get_template_part('template-parts/sections/content', null, array(
    'layout' => 'centered',
    'background' => 'primary',
    'padding' => 'xl',
    'headline' => 'Ready to Find Your Happy Place?',
    'content' => 'This path to homeownership is filled with possibilities, decisions, and meaningful moments that we\'re honored to share with you. Let our experienced Delaware team help you navigate the real estate market with confidence.',
    'buttons' => array(
        array(
            'text' => 'Start Your Search',
            'url' => '/properties/',
            'style' => 'outline-white',
            'size' => 'lg',
            'icon' => 'fas fa-search'
        ),
        array(
            'text' => 'Contact Us Today',
            'url' => '/contact/',
            'style' => 'white',
            'size' => 'lg',
            'icon' => 'fas fa-phone'
        )
    ),
    'section_id' => 'cta'
));

// Contact Section
get_template_part('template-parts/sections/content', null, array(
    'layout' => 'centered',
    'background' => 'white',
    'padding' => 'xl',
    'badge' => 'Get In Touch',
    'headline' => 'Contact Our Expert Team',
    'subheadline' => 'Serving Georgetown, Milford, Middletown & Surrounding Delaware Communities',
    'content' => 'Have questions about buying, selling, or investing in Delaware real estate? Our commitment to you continues long after settlement day — we\'re just a phone call away.',
    'buttons' => array(
        array(
            'text' => 'Call (302) 217-6692',
            'url' => 'tel:3022176692',
            'style' => 'primary',
            'size' => 'lg',
            'icon' => 'fas fa-phone'
        ),
        array(
            'text' => 'Email cheers@theparkergroup.com',
            'url' => 'mailto:cheers@theparkergroup.com',
            'style' => 'outline',
            'size' => 'lg',
            'icon' => 'fas fa-envelope'
        )
    ),
    'section_id' => 'contact'
));

// Service Areas Section with Image Background
get_template_part('template-parts/sections/hero', null, array(
    'style' => 'image',
    'height' => 'md',
    'background_image' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 31.jpg',
    'overlay' => 'gradient',
    'overlay_opacity' => '60',
    'alignment' => 'center',
    'content_width' => 'normal',
    'badge' => 'Service Areas',
    'headline' => 'Serving All of Delaware',
    'subheadline' => 'Georgetown • Milford • Middletown • Sussex • Kent • New Castle Counties',
    'content' => 'As locals, we bring community insights that only Delaware experts can provide. Whether you\'re looking in coastal communities like Rehoboth and Lewes or growing areas like Dover and Smyrna, we know the neighborhoods inside and out.',
    'buttons' => array(
        array(
            'text' => 'View Service Areas',
            'url' => '/areas/',
            'style' => 'white',
            'size' => 'xl',
            'icon' => 'fas fa-map-marker-alt'
        )
    ),
    'section_id' => 'service-areas'
));

// Testimonials Section
get_template_part('template-parts/sections/content', null, array(
    'layout' => 'full-width',
    'background' => 'light',
    'padding' => 'xl',
    'badge' => 'Client Stories',
    'headline' => 'What Our Happy Place Families Say',
    'content' => 'Don\'t just take our word for it — hear from the Delaware families we\'ve helped find their happy place. From first-time buyers to growing families to empty nesters, we\'ve been honored to be part of their journey.',
    'buttons' => array(
        array(
            'text' => 'Read More Stories',
            'url' => '/testimonials/',
            'style' => 'primary',
            'size' => 'md',
            'icon' => 'fas fa-heart'
        )
    ),
    'section_id' => 'testimonials'
));

// Process Section with Left Image
get_template_part('template-parts/sections/content', null, array(
    'layout' => 'left-image',
    'background' => 'white',
    'padding' => 'xl',
    'image' => array(
        'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 12.jpg',
        'alt' => 'Beautiful Delaware home kitchen',
        'width' => 600,
        'height' => 400
    ),
    'badge' => 'Our Process',
    'headline' => 'From Contract to Keys',
    'subheadline' => '10 Steps to Your Happy Place',
    'content' => 'We combine market knowledge, understanding of seller perspectives, and property value assessment to craft strong offers that work for you. This careful process protects your investment while keeping you informed every step of the way.',
    'buttons' => array(
        array(
            'text' => 'View Full Process',
            'url' => '/process/',
            'style' => 'primary',
            'size' => 'md',
            'icon' => 'fas fa-list-ol'
        ),
        array(
            'text' => 'Download Guide',
            'url' => '/guide/',
            'style' => 'outline',
            'size' => 'md',
            'icon' => 'fas fa-download'
        )
    ),
    'section_id' => 'process'
));

// Newsletter Section with Secondary Background
get_template_part('template-parts/sections/content', null, array(
    'layout' => 'left',
    'background' => 'secondary',
    'padding' => 'lg',
    'badge' => 'Stay Connected',
    'headline' => 'Delaware Market Updates',
    'content' => 'Get monthly insights about the Delaware real estate market, new listings in your favorite neighborhoods, and tips for buyers and sellers.',
    'buttons' => array(
        array(
            'text' => 'Subscribe to Updates',
            'url' => '/newsletter/',
            'style' => 'white',
            'size' => 'lg',
            'icon' => 'fas fa-envelope'
        )
    ),
    'section_id' => 'newsletter'
));

// Additional Hero Examples - Showcasing Different Variations

// Local Expertise Hero - Image Background with Gradient Overlay
get_template_part('template-parts/sections/hero', null, array(
    'style' => 'image',
    'height' => 'md',
    'background_image' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 20.jpg',
    'overlay' => 'gradient',
    'overlay_opacity' => '70',
    'alignment' => 'left',
    'content_width' => 'wide',
    'badge' => 'Local Expertise',
    'headline' => 'Georgetown, Milford & Middletown',
    'subheadline' => 'Deep roots in Delaware communities',
    'content' => 'As locals, we bring community insights that only Delaware experts can provide. Our commitment to you continues long after settlement day.',
    'buttons' => array(
        array(
            'text' => 'View Our Areas',
            'url' => '/areas/',
            'style' => 'white',
            'size' => 'xl',
            'icon' => 'fas fa-map-marker-alt'
        )
    ),
    'section_id' => 'local-hero'
));

// Process Hero - Image Background with Gradient Overlay
get_template_part('template-parts/sections/hero', null, array(
    'style' => 'image',
    'height' => 'xl',
    'background_image' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 36.jpg',
    'overlay' => 'gradient',
    'overlay_opacity' => '60',
    'alignment' => 'right',
    'content_width' => 'narrow',
    'headline' => 'Your Settlement Day Success',
    'subheadline' => 'From Contract to Keys',
    'content' => 'Final walk-through, document signing, and receiving the keys to your happy place! We\'re here to celebrate this meaningful moment with you.',
    'buttons' => array(
        array(
            'text' => 'Our Process',
            'url' => '/process/',
            'style' => 'outline-white',
            'size' => 'xl',
            'icon' => 'fas fa-key'
        )
    ),
    'section_id' => 'process-hero'
));

// Testimonials Hero - Image Background with Gradient Overlay
get_template_part('template-parts/sections/hero', null, array(
    'style' => 'image',
    'height' => 'sm',
    'background_image' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 43.jpg',
    'overlay' => 'gradient',
    'overlay_opacity' => '50',
    'alignment' => 'center',
    'content_width' => 'normal',
    'badge' => 'Client Stories',
    'headline' => 'What Our Happy Place Families Say',
    'content' => 'Don\'t just take our word for it — hear from the Delaware families we\'ve helped find their happy place.',
    'buttons' => array(
        array(
            'text' => 'Read Testimonials',
            'url' => '/testimonials/',
            'style' => 'white',
            'size' => 'xl',
            'icon' => 'fas fa-heart'
        )
    ),
    'section_id' => 'testimonials-hero'
));

// Minimal Hero - Clean and Simple
get_template_part('template-parts/sections/hero', null, array(
    'style' => 'minimal',
    'height' => 'sm',
    'overlay' => 'none',
    'alignment' => 'center',
    'content_width' => 'narrow',
    'headline' => 'Ready to Get Started?',
    'content' => 'Contact The Parker Group today to begin your journey to homeownership in Delaware.',
    'buttons' => array(
        array(
            'text' => 'Contact Us Today',
            'url' => '/contact/',
            'style' => 'primary',
            'size' => 'xl',
            'icon' => 'fas fa-phone'
        ),
        array(
            'text' => 'Schedule Consultation',
            'url' => '/schedule/',
            'style' => 'outline',
            'size' => 'xl',
            'icon' => 'fas fa-calendar'
        )
    ),
    'section_id' => 'cta-hero'
));

get_footer();
?>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "RealEstateAgent",
    "name": "The Parker Group",
    "description": "Your trusted Delaware real estate partners helping you find your happy place in Georgetown, Milford, Middletown and surrounding communities.",
    "url": "<?php echo esc_url(home_url()); ?>",
    "telephone": "302-217-6692",
    "email": "cheers@theparkergroup.com",
    "address": {
        "@type": "PostalAddress",
        "streetAddress": "673 N. Bedford St.",
        "addressLocality": "Georgetown",
        "addressRegion": "DE",
        "postalCode": "19947",
        "addressCountry": "US"
    },
    "areaServed": [
        "Georgetown, DE",
        "Milford, DE", 
        "Middletown, DE",
        "Sussex County, DE",
        "Kent County, DE",
        "New Castle County, DE"
    ],
    "hasOfferCatalog": {
        "@type": "OfferCatalog",
        "name": "Real Estate Listings",
        "itemListElement": [
            {
                "@type": "Offer",
                "name": "Residential Properties"
            },
            {
                "@type": "Offer", 
                "name": "Commercial Properties"
            },
            {
                "@type": "Offer",
                "name": "Investment Properties"
            }
        ]
    }
}
</script>

<?php
get_footer();

/**
 * Enqueue homepage-specific assets
 * These are loaded only on the front page for optimal performance
 */
function hph_enqueue_homepage_assets() {
    if (is_front_page()) {
        // Homepage-specific CSS
        wp_enqueue_style(
            'hph-homepage',
            get_template_directory_uri() . '/assets/css/pages/homepage.css',
            array('hph-framework'),
get_theme_mod('theme_version', '3.0.0')
        );
        
        // Homepage-specific JavaScript
        wp_enqueue_script(
            'hph-homepage-js',
            get_template_directory_uri() . '/assets/js/pages/homepage.js',
            array('jquery'),
get_theme_mod('theme_version', '3.0.0'),
            true
        );
        
        // Localize script with AJAX data
        wp_localize_script('hph-homepage-js', 'hph_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hph_homepage_nonce'),
            'strings' => array(
                'loading' => __('Loading...', 'happy-place-theme'),
                'error' => __('Something went wrong. Please try again.', 'happy-place-theme'),
                'success' => __('Thank you! We will be in touch soon.', 'happy-place-theme')
            )
        ));
    }
}
add_action('wp_enqueue_scripts', 'hph_enqueue_homepage_assets');

/**
 * Add custom body class for homepage styling
 */
function hph_homepage_body_class($classes) {
    if (is_front_page()) {
        $classes[] = 'hph-homepage';
        $classes[] = 'hph-modern-layout';
    }
    return $classes;
}
add_filter('body_class', 'hph_homepage_body_class');
