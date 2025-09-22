<?php
/**
 * Template Name: Contact Page
 * Description: Contact page built entirely with template parts
 *
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header(); ?>

<main id="main-content" class="hph-site-main">

    <?php
    // ============================================
    // Contact Hero Section
    // ============================================
    get_template_part('template-parts/sections/hero', null, [
        'style' => 'image',
        'theme' => 'primary',
        'height' => 'lg',
        'is_top_of_page' => true,
        'background_image' => hph_add_fastly_optimization(hph_get_image_url_only('assets/images/hero-bg3.jpg'), 'full'),
        'ken_burns' => true,
        'ken_burns_direction' => 'pan-left',
        'ken_burns_duration' => 20,
        'overlay' => 'dark',
        'alignment' => 'left',
        'headline' => 'Get in Touch',
        'subheadline' => 'We\'re here to help you find your happy place',
        'content' => 'Visit one of our three Delaware offices or reach out today to start your real estate journey with our team of 50+ experienced agents.',
        'content_width' => 'narrow',
        'buttons' => [
            [
                'text' => 'Call Us Now',
                'url' => 'tel:+13022176692',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-phone'
            ],
            [
                'text' => 'Send a Message',
                'url' => '#',
                'style' => 'outline-white',
                'size' => 'xl',
                'icon' => 'fas fa-envelope',
                'data_attributes' => 'class="modal-trigger" data-modal-form="general-contact" data-modal-title="Contact Us" data-modal-subtitle="Send us a message and we\'ll get back to you soon."'
            ]
        ],
        'section_id' => 'contact-hero'
    ]);
    ?>

    <?php
    // ============================================
    // Office Locations Section
    // ============================================
    get_template_part('template-parts/sections/content', null, [
        'layout' => 'centered',
        'background' => 'light',
        'padding' => '2xl',
        'alignment' => 'center',
        'badge' => 'Our Locations',
        'headline' => 'Visit Our Delaware Offices',
        'subheadline' => 'Stop by any of our three convenient locations throughout Delaware for personalized service and local expertise.',
        'features' => [
            [
                'icon' => 'fas fa-building',
                'title' => 'Georgetown Office',
                'subtitle' => 'Main Office',
                'content' => '<div class="hph-text-muted hph-mb-4">
                    <p class="hph-mb-1">673 N. Bedford St.</p>
                    <p class="hph-mb-3">Georgetown, DE 19947</p>
                    <div class="hph-flex hph-gap-2 hph-justify-center">
                        <a href="tel:+13022176692" class="hph-btn hph-btn-outline-primary hph-btn-sm">
                            <i class="fas fa-phone hph-mr-2"></i>Call
                        </a>
                        <a href="#" class="hph-btn hph-btn-primary hph-btn-sm modal-trigger" data-modal-form="general-contact" data-modal-title="Contact Georgetown Office" data-modal-subtitle="Send a message to our Georgetown team.">
                            <i class="fas fa-envelope hph-mr-2"></i>Message
                        </a>
                    </div>
                </div>'
            ],
            [
                'icon' => 'fas fa-map-marker-alt',
                'title' => 'Milford Office',
                'content' => '<div class="hph-text-muted hph-mb-4">
                    <p class="hph-mb-1">48 N. Walnut St.</p>
                    <p class="hph-mb-3">Milford, DE 19963</p>
                    <div class="hph-flex hph-gap-2 hph-justify-center">
                        <a href="tel:+13022176692" class="hph-btn hph-btn-outline-primary hph-btn-sm">
                            <i class="fas fa-phone hph-mr-2"></i>Call
                        </a>
                        <a href="#" class="hph-btn hph-btn-primary hph-btn-sm modal-trigger" data-modal-form="general-contact" data-modal-title="Contact Milford Office" data-modal-subtitle="Send a message to our Milford team.">
                            <i class="fas fa-envelope hph-mr-2"></i>Message
                        </a>
                    </div>
                </div>'
            ],
            [
                'icon' => 'fas fa-map-marker-alt',
                'title' => 'Middletown Office',
                'content' => '<div class="hph-text-muted hph-mb-4">
                    <p class="hph-mb-1">108 Patriot Dr.</p>
                    <p class="hph-mb-3">Middletown, DE 19709</p>
                    <div class="hph-flex hph-gap-2 hph-justify-center">
                        <a href="tel:+13022176692" class="hph-btn hph-btn-outline-primary hph-btn-sm">
                            <i class="fas fa-phone hph-mr-2"></i>Call
                        </a>
                        <a href="#" class="hph-btn hph-btn-primary hph-btn-sm modal-trigger" data-modal-form="general-contact" data-modal-title="Contact Middletown Office" data-modal-subtitle="Send a message to our Middletown team.">
                            <i class="fas fa-envelope hph-mr-2"></i>Message
                        </a>
                    </div>
                </div>'
            ]
        ],
        'features_columns' => 3,
        'section_id' => 'office-locations'
    ]);
    ?>

    <?php
    // ============================================
    // Contact Form Section
    // ============================================
    get_template_part('template-parts/sections/content', null, [
        'layout' => 'left-form',
        'background' => 'white',
        'padding' => '2xl',
        'alignment' => 'left',
        'badge' => 'Contact Us',
        'headline' => 'Send Us a Message',
        'subheadline' => 'Ready to start your real estate journey?',
        'content' => 'Whether you\'re buying your first home, selling a property, or just have questions about the Delaware real estate market, our team of 50+ agents is here to provide expert guidance with no pressure.',
'form_content' => (function() {
            ob_start();
            get_template_part('template-parts/forms/general-contact', null, [
                'variant' => 'default',
                'modal_context' => false,
                'title' => '',
                'description' => '',
                'submit_text' => 'Send Message',
                'show_office_info' => false,
                'department_routing' => true,
                'layout' => 'vertical',
                'size' => 'normal'
            ]);
            return ob_get_clean();
        })(),
        'features' => [
            [
                'icon' => 'fas fa-comments',
                'title' => 'Free Consultation',
                'content' => 'No cost, no pressure - just honest advice from our experienced team'
            ],
            [
                'icon' => 'fas fa-map-marked-alt',
                'title' => 'Local Market Expertise',
                'content' => 'Deep knowledge of Delaware communities and neighborhoods'
            ],
            [
                'icon' => 'fas fa-handshake',
                'title' => 'No Pressure Approach',
                'content' => 'We listen to your needs first, then provide tailored solutions'
            ],
            [
                'icon' => 'fas fa-users',
                'title' => '50+ Agents Ready',
                'content' => 'Our full team of experts working together for your success'
            ]
        ],
        'section_id' => 'contact-form'
    ]);
    ?>

    <?php
    // ============================================
    // Why Choose Us Stats Section
    // ============================================
    get_template_part('template-parts/sections/stats', null, [
        'background' => 'light',
        'padding' => '2xl',
        'badge' => 'Why Choose Us',
        'headline' => 'Trusted Delaware Real Estate Experts',
        'subheadline' => 'Our track record speaks for itself',
        'stats' => [
            [
                'number' => '2,755+',
                'label' => 'Homes Sold',
                'icon' => 'fas fa-home',
                'description' => 'Successfully closed transactions'
            ],
            [
                'number' => '$922M+',
                'label' => 'Total Sales Volume',
                'icon' => 'fas fa-chart-line',
                'description' => 'In real estate sales'
            ],
            [
                'number' => '51%',
                'label' => 'Faster Than Average',
                'icon' => 'fas fa-clock',
                'description' => 'Time to sell vs market average'
            ],
            [
                'number' => '50+',
                'label' => 'Expert Agents',
                'icon' => 'fas fa-users',
                'description' => 'Ready to help you succeed'
            ]
        ],
        'columns' => 4,
        'animate_counters' => true,
        'section_id' => 'contact-stats'
    ]);
    ?>

    <?php
    // ============================================
    // CTA Section
    // ============================================
    get_template_part('template-parts/sections/cta', null, [
        'layout' => 'centered',
        'background' => 'primary',
        'padding' => '2xl',
        'badge' => 'Ready to Get Started?',
        'headline' => 'Let\'s Find Your Happy Place',
        'subheadline' => 'Contact us today and let our expert team guide you through your real estate journey',
        'buttons' => [
            [
                'text' => 'Call Us Now',
                'url' => 'tel:+13022176692',
                'style' => 'white',
                'size' => 'lg',
                'icon' => 'fas fa-phone'
            ],
            [
                'text' => 'Browse Properties',
                'url' => '/listings/',
                'style' => 'outline-white',
                'size' => 'lg',
                'icon' => 'fas fa-search'
            ]
        ],
        'section_id' => 'contact-cta'
    ]);
    ?>

</main>

<?php get_footer(); ?>