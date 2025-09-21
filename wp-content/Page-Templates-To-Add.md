<?php
/**
 * Template Name: About Page
 * Description: Company/Agency about page with team, values, and story sections
 */

get_header(); ?>

<main id="main-content" class="site-main">
    
    <?php
    // ============================================
    // About Hero Section
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'gradient',
        'theme' => 'ocean',
        'height' => 'lg',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('parker-group-team.jpg') : '',
        'parallax' => true,
        'overlay' => 'gradient',
        'alignment' => 'center',
        'headline' => 'About The Parker Group',
        'subheadline' => 'Helping Delaware Find Its Happy Place Since 2016',
        'content' => 'We believe selling or buying a home should be an experience filled with confidence, clarity, and genuine care. Our team approach means you\'ll never feel alone in this process.',
        'content_width' => 'normal',
        'fade_in' => true,
        'scroll_indicator' => true,
        'buttons' => array(
            array(
                'text' => 'Meet Our Team',
                'url' => '#team-section',
                'style' => 'white',
                'size' => 'l',
                'icon' => 'fas fa-users',
                'icon_position' => 'right',
                'target' => '_self'
            ),
            array(
                'text' => 'Contact Us',
                'url' => '/contact',
                'style' => 'outline-white',
                'size' => 'l',
                'target' => '_self'
            )
        ),
        'section_id' => 'about-hero'
    ));
    
    // ============================================
    // Company Story Section
    // ============================================
    get_template_part('template-parts/sections/content', null, array(
        'style' => 'split',
        'theme' => 'light',
        'layout' => 'image-left',
        'padding' => 'xl',
        'container' => 'default',
        'image' => function_exists('hph_get_image_url') ? hph_get_image_url('dustin-rachel-parker.jpg') : '',
        'image_style' => 'rounded',
        'headline' => 'Our Story',
        'subheadline' => 'Founded with One Simple Goal',
        'content' => '<p>When Dustin and Rachel Parker founded The Parker Group in 2016, they had one simple goal: to serve the community they love by helping people find their happy place. What started as a small team has grown into something remarkable—a full-service brokerage that\'s transforming how real estate works, one home at a time.</p>
        <p>We measure success not by how many homes we sell, but by how well we serve each client who trusts us with their journey. With over 50 agents working together, we bring a team approach that ensures you get the best of everyone\'s expertise.</p>',
        'buttons' => array(
            array(
                'text' => 'Learn More',
                'url' => '#values',
                'style' => 'primary',
                'size' => 'm',
                'icon' => 'fas fa-arrow-right',
                'icon_position' => 'right'
            )
        ),
        'section_id' => 'company-story'
    ));
    
    // ============================================
    // Core Values Section
    // ============================================
    get_template_part('template-parts/sections/features', null, array(
        'style' => 'cards',
        'theme' => 'white',
        'columns' => 4,
        'icon_style' => 'circle',
        'padding' => 'xl',
        'headline' => 'Our Core Values',
        'subheadline' => 'The principles that guide everything we do',
        'features' => array(
            array(
                'icon' => 'fas fa-home',
                'title' => 'Happy Places',
                'description' => 'We believe everyone deserves to find their happy place, whether buying or selling.'
            ),
            array(
                'icon' => 'fas fa-users',
                'title' => 'Team First',
                'description' => 'Our collaborative approach means you get the expertise of 50+ agents working for you.'
            ),
            array(
                'icon' => 'fas fa-heart',
                'title' => 'Community',
                'description' => 'We\'re committed to giving back to the Delaware communities we call home.'
            ),
            array(
                'icon' => 'fas fa-chart-line',
                'title' => 'Results',
                'description' => 'With $922M in sales and 2,755 homes sold, our track record speaks for itself.'
            )
        ),
        'section_id' => 'values'
    ));
    
    // ============================================
    // Team Section
    // ============================================
    get_template_part('template-parts/sections/team', null, array(
        'style' => 'grid',
        'theme' => 'light',
        'columns' => 3,
        'padding' => 'xl',
        'headline' => 'Meet The Leadership',
        'subheadline' => 'The experts dedicated to your success',
        'show_social' => true,
        'team_members' => array(
            array(
                'name' => 'Dustin Parker',
                'position' => 'Co-Founder',
                'image' => function_exists('hph_get_image_url') ? hph_get_image_url('dustin-parker.jpg') : '',
                'bio' => 'Dustin brings innovative strategies and a passion for helping families find their perfect homes.',
                'social' => array(
                    'linkedin' => '#',
                    'instagram' => '#'
                )
            ),
            array(
                'name' => 'Rachel Parker',
                'position' => 'Co-Founder',
                'image' => function_exists('hph_get_image_url') ? hph_get_image_url('rachel-parker.jpg') : '',
                'bio' => 'Rachel\'s dedication to client care and community involvement drives our mission forward.',
                'social' => array(
                    'linkedin' => '#',
                    'instagram' => '#'
                )
            ),
            array(
                'name' => 'Your Agent',
                'position' => 'Real Estate Professional',
                'image' => function_exists('hph_get_image_url') ? hph_get_image_url('agent-placeholder.jpg') : '',
                'bio' => 'Each of our 50+ agents brings unique expertise and local knowledge to serve you better.',
                'social' => array(
                    'linkedin' => '#'
                )
            )
        ),
        'section_id' => 'team-section'
    ));
    
    // ============================================
    // Stats/Achievements Section
    // ============================================
    get_template_part('template-parts/sections/stats', null, array(
        'style' => 'counters',
        'theme' => 'ocean',
        'padding' => 'lg',
        'background_pattern' => 'dots',
        'stats' => array(
            array(
                'number' => '2,755',
                'label' => 'Homes Sold',
                'icon' => 'fas fa-home'
            ),
            array(
                'number' => '$922M',
                'label' => 'Total Volume',
                'icon' => 'fas fa-dollar-sign'
            ),
            array(
                'number' => '51%',
                'label' => 'Faster Sales',
                'icon' => 'fas fa-clock'
            ),
            array(
                'number' => '50+',
                'label' => 'Team Members',
                'icon' => 'fas fa-users'
            )
        ),
        'section_id' => 'achievements'
    ));
    
    // ============================================
    // Call to Action
    // ============================================
    get_template_part('template-parts/sections/cta', null, array(
        'style' => 'centered',
        'theme' => 'gradient',
        'padding' => 'xl',
        'headline' => 'Ready to Find Your Happy Place?',
        'subheadline' => 'Let\'s start your real estate journey together',
        'buttons' => array(
            array(
                'text' => 'Get Started Today',
                'url' => '/contact',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-arrow-right',
                'icon_position' => 'right'
            ),
            array(
                'text' => 'Call: 302.217.6692',
                'url' => 'tel:3022176692',
                'style' => 'outline-white',
                'size' => 'xl',
                'icon' => 'fas fa-phone',
                'icon_position' => 'left'
            )
        ),
        'section_id' => 'about-cta'
    ));
    ?>
    
</main>

<?php get_footer(); ?>

<?php
/**
 * Template Name: Contact Page
 * Description: Contact page with form, map, and contact information
 */

get_header(); ?>

<main id="main-content" class="site-main">
    
    <?php
    // ============================================
    // Contact Hero Section
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'minimal',
        'theme' => 'light',
        'height' => 'md',
        'alignment' => 'center',
        'headline' => 'Get in Touch',
        'subheadline' => 'We\'re here to help you find your happy place',
        'content' => 'Visit one of our three Delaware offices or reach out today to start your real estate journey.',
        'content_width' => 'narrow',
        'fade_in' => true,
        'section_id' => 'contact-hero'
    ));
    
    // ============================================
    // Contact Information Cards
    // ============================================
    get_template_part('template-parts/sections/features', null, array(
        'style' => 'cards',
        'theme' => 'white',
        'columns' => 3,
        'icon_style' => 'filled',
        'padding' => 'lg',
        'features' => array(
            array(
                'icon' => 'fas fa-map-marker-alt',
                'title' => 'Georgetown Office',
                'description' => '673 N. Bedford St.<br>Georgetown, DE 19947',
                'link' => array(
                    'text' => 'Get Directions',
                    'url' => 'https://maps.google.com/?q=673+N+Bedford+St+Georgetown+DE',
                    'style' => 'text'
                )
            ),
            array(
                'icon' => 'fas fa-map-marker-alt',
                'title' => 'Milford Office',
                'description' => '48 N. Walnut St.<br>Milford, DE 19963',
                'link' => array(
                    'text' => 'Get Directions',
                    'url' => 'https://maps.google.com/?q=48+N+Walnut+St+Milford+DE',
                    'style' => 'text'
                )
            ),
            array(
                'icon' => 'fas fa-map-marker-alt',
                'title' => 'Middletown Office',
                'description' => '108 Patriot Dr.<br>Middletown, DE 19709',
                'link' => array(
                    'text' => 'Get Directions',
                    'url' => 'https://maps.google.com/?q=108+Patriot+Dr+Middletown+DE',
                    'style' => 'text'
                )
            )
        ),
        'section_id' => 'office-locations'
    ));
    
    // ============================================
    // Contact Methods
    // ============================================
    get_template_part('template-parts/sections/features', null, array(
        'style' => 'icons-left',
        'theme' => 'light',
        'columns' => 2,
        'padding' => 'lg',
        'features' => array(
            array(
                'icon' => 'fas fa-phone',
                'title' => 'Call Us',
                'description' => '<strong>302.217.6692</strong><br>Monday-Friday: 9AM-6PM EST<br>Saturday: 10AM-4PM EST'
            ),
            array(
                'icon' => 'fas fa-envelope',
                'title' => 'Email Us',
                'description' => '<strong>cheers@theparkergroup.com</strong><br>We\'ll respond within 24 hours'
            ),
            array(
                'icon' => 'fas fa-globe',
                'title' => 'Website',
                'description' => '<strong>theparkergroup.com</strong><br>Browse listings and resources online'
            ),
            array(
                'icon' => 'fab fa-instagram',
                'title' => 'Follow Us',
                'description' => '<strong>@the_parker_group</strong><br><strong>@theparkergroupsocial</strong>'
            )
        ),
        'section_id' => 'contact-methods'
    ));
    
    // ============================================
    // Contact Form Section
    // ============================================
    get_template_part('template-parts/sections/form', null, array(
        'style' => 'split',
        'theme' => 'white',
        'layout' => 'form-right',
        'padding' => 'xl',
        'form_id' => 'contact-form',
        'headline' => 'Send Us a Message',
        'subheadline' => 'Let\'s start a conversation about your real estate needs',
        'content' => '<p>Whether you\'re buying your first home, selling a property, or just have questions about the Delaware real estate market, we\'re here to help.</p>
        <ul class="list-check">
            <li>Free consultation</li>
            <li>Local market expertise</li>
            <li>No pressure, just answers</li>
            <li>50+ agents ready to help</li>
        </ul>',
        'form_fields' => array(
            array(
                'type' => 'text',
                'name' => 'name',
                'label' => 'Full Name',
                'required' => true,
                'width' => 'half'
            ),
            array(
                'type' => 'email',
                'name' => 'email',
                'label' => 'Email Address',
                'required' => true,
                'width' => 'half'
            ),
            array(
                'type' => 'tel',
                'name' => 'phone',
                'label' => 'Phone Number',
                'required' => false,
                'width' => 'half'
            ),
            array(
                'type' => 'select',
                'name' => 'interest',
                'label' => 'I\'m interested in...',
                'required' => true,
                'width' => 'half',
                'options' => array(
                    'buying' => 'Buying a Home',
                    'selling' => 'Selling a Home',
                    'both' => 'Both Buying & Selling',
                    'general' => 'General Question'
                )
            ),
            array(
                'type' => 'textarea',
                'name' => 'message',
                'label' => 'How can we help you find your happy place?',
                'required' => true,
                'width' => 'full',
                'rows' => 5
            )
        ),
        'submit_text' => 'Send Message',
        'section_id' => 'contact-form-section'
    ));
    
    // ============================================
    // Map Section
    // ============================================
    get_template_part('template-parts/sections/map', null, array(
        'style' => 'full',
        'height' => 'md',
        'api_key' => 'YOUR_GOOGLE_MAPS_API_KEY',
        'latitude' => '38.7745',
        'longitude' => '-75.4621',
        'zoom' => 12,
        'marker_title' => 'The Parker Group - Georgetown',
        'info_window' => '<strong>The Parker Group</strong><br>673 N. Bedford St.<br>Georgetown, DE 19947',
        'section_id' => 'contact-map'
    ));
    ?>
    
</main>

<?php get_footer(); ?>

<?php
/**
 * Template Name: Landing Page - Home Buyer Guide
 * Description: Landing page for home buyer guide download (Happy Place Handbook)
 */

get_header(); ?>

<main id="main-content" class="site-main">
    
    <?php
    // ============================================
    // Hero with Guide Download Form
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'split-form',
        'theme' => 'ocean',
        'height' => 'lg',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('happy-home-bg.jpg') : '',
        'overlay' => 'gradient',
        'alignment' => 'left',
        'headline' => 'Find Your Happy Place',
        'subheadline' => 'Get Your Free Home Buyer\'s Handbook',
        'content' => 'Nobody ever said buying a home was simple, but that doesn\'t mean it has to be hard. Download our comprehensive guide to navigate your home buying journey with confidence.',
        'bullets' => array(
            '10-step home buying process',
            'Local Delaware market insights',
            'Financing tips from trusted lenders',
            'Inspection checklists & negotiation strategies'
        ),
        'form' => array(
            'headline' => 'Download Your Free Guide',
            'fields' => array(
                array(
                    'type' => 'text',
                    'name' => 'first_name',
                    'placeholder' => 'First Name',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'name' => 'last_name',
                    'placeholder' => 'Last Name',
                    'required' => true
                ),
                array(
                    'type' => 'email',
                    'name' => 'email',
                    'placeholder' => 'Email Address',
                    'required' => true
                ),
                array(
                    'type' => 'tel',
                    'name' => 'phone',
                    'placeholder' => 'Phone Number',
                    'required' => true
                ),
                array(
                    'type' => 'select',
                    'name' => 'timeline',
                    'label' => 'When are you looking to buy?',
                    'options' => array(
                        'asap' => 'ASAP',
                        '1-3months' => '1-3 Months',
                        '3-6months' => '3-6 Months',
                        '6-12months' => '6-12 Months',
                        'research' => 'Just Researching'
                    ),
                    'required' => true
                )
            ),
            'submit_text' => 'Send Me The Guide',
            'privacy_text' => 'Your information is safe with us. We\'ll also send helpful tips for your home search.'
        ),
        'section_id' => 'buyer-guide-hero'
    ));
    
    // ============================================
    // What's Inside Section
    // ============================================
    get_template_part('template-parts/sections/features', null, array(
        'style' => 'grid',
        'theme' => 'white',
        'columns' => 3,
        'padding' => 'xl',
        'headline' => 'What\'s Inside The Happy Place Handbook',
        'subheadline' => 'Your complete roadmap to homeownership',
        'features' => array(
            array(
                'icon' => 'fas fa-map-marked-alt',
                'title' => '10-Step Journey',
                'description' => 'From pre-approval to closing day, we break down every step of your home buying journey.'
            ),
            array(
                'icon' => 'fas fa-calculator',
                'title' => 'Financial Guidance',
                'description' => 'Understand mortgages, closing costs, and smart financing strategies for your budget.'
            ),
            array(
                'icon' => 'fas fa-search-location',
                'title' => 'Local Market Insights',
                'description' => 'Get insider knowledge about Delaware neighborhoods and market conditions.'
            ),
            array(
                'icon' => 'fas fa-clipboard-check',
                'title' => 'Inspection Tips',
                'description' => 'Know what to look for and what questions to ask during home inspections.'
            ),
            array(
                'icon' => 'fas fa-file-signature',
                'title' => 'Negotiation Strategies',
                'description' => 'Learn how to make competitive offers that get accepted.'
            ),
            array(
                'icon' => 'fas fa-key',
                'title' => 'Settlement Checklist',
                'description' => 'Be fully prepared for your closing day with our comprehensive checklist.'
            )
        ),
        'section_id' => 'guide-contents'
    ));
    
    // ============================================
    // Process Timeline
    // ============================================
    get_template_part('template-parts/sections/process', null, array(
        'style' => 'timeline',
        'theme' => 'light',
        'padding' => 'xl',
        'headline' => 'Your Path to Homeownership',
        'subheadline' => 'We\'re with you every step of the way',
        'steps' => array(
            array(
                'number' => '1',
                'title' => 'Pre-Approval',
                'description' => 'Get pre-approved to know your budget and show sellers you\'re serious.'
            ),
            array(
                'number' => '2',
                'title' => 'Home Search',
                'description' => 'Define your criteria and explore neighborhoods that match your lifestyle.'
            ),
            array(
                'number' => '3',
                'title' => 'Make an Offer',
                'description' => 'Craft a competitive offer with your agent\'s expert guidance.'
            ),
            array(
                'number' => '4',
                'title' => 'Settlement',
                'description' => 'Navigate inspections, appraisals, and paperwork to reach closing day.'
            )
        ),
        'section_id' => 'buying-process'
    ));
    
    // ============================================
    // About Parker Group
    // ============================================
    get_template_part('template-parts/sections/content', null, array(
        'style' => 'split',
        'theme' => 'white',
        'layout' => 'image-right',
        'padding' => 'xl',
        'image' => function_exists('hph_get_image_url') ? hph_get_image_url('parker-team.jpg') : '',
        'headline' => 'Why The Parker Group?',
        'subheadline' => 'Local Experts, Proven Results',
        'content' => '<p>With over 50 agents and $922M in total sales volume, The Parker Group is Delaware\'s trusted real estate partner. We\'re not just agents—we\'re your neighbors, committed to helping you find your happy place.</p>
        <ul>
            <li>2,755+ homes sold</li>
            <li>51% faster sales than average</li>
            <li>Award-winning service</li>
            <li>3 convenient Delaware locations</li>
        </ul>',
        'buttons' => array(
            array(
                'text' => 'Meet Our Team',
                'url' => '/about',
                'style' => 'primary',
                'size' => 'm'
            )
        ),
        'section_id' => 'about-parker'
    ));
    
    // ============================================
    // Testimonials
    // ============================================
    get_template_part('template-parts/sections/testimonials', null, array(
        'style' => 'carousel',
        'theme' => 'light',
        'padding' => 'xl',
        'headline' => 'Happy Homeowners',
        'subheadline' => 'Real stories from real clients',
        'testimonials' => array(
            array(
                'content' => 'The Parker Group made our first home purchase so easy! The Happy Place Handbook answered all our questions before we even asked them.',
                'author' => 'Sarah & Tom Mitchell',
                'position' => 'First-Time Buyers, Georgetown',
                'rating' => 5
            ),
            array(
                'content' => 'Professional, knowledgeable, and genuinely caring. They helped us find our dream home in just 3 weeks!',
                'author' => 'The Johnson Family',
                'position' => 'Relocated to Milford',
                'rating' => 5
            ),
            array(
                'content' => 'From the first consultation to closing day, everything was seamless. Highly recommend!',
                'author' => 'Michael Chen',
                'position' => 'Middletown Homeowner',
                'rating' => 5
            )
        ),
        'section_id' => 'testimonials'
    ));
    
    // ============================================
    // Final CTA
    // ============================================
    get_template_part('template-parts/sections/cta', null, array(
        'style' => 'centered',
        'theme' => 'ocean',
        'padding' => 'xl',
        'headline' => 'Ready to Find Your Happy Place?',
        'subheadline' => 'Download your free guide and start your journey today',
        'buttons' => array(
            array(
                'text' => 'Get The Guide',
                'url' => '#buyer-guide-hero',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-download',
                'icon_position' => 'right'
            ),
            array(
                'text' => 'Call: 302.217.6692',
                'url' => 'tel:3022176692',
                'style' => 'outline-white',
                'size' => 'xl',
                'icon' => 'fas fa-phone',
                'icon_position' => 'left'
            )
        ),
        'section_id' => 'guide-cta'
    ));
    ?>
    
</main>

<?php get_footer(); ?>

<?php
/**
 * Template Name: Landing Page - Home Valuation
 * Description: Landing page for instant home valuation requests
 */

get_header(); ?>

<main id="main-content" class="site-main">
    
    <?php
    // ============================================
    // Hero with Valuation CTA
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'gradient',
        'theme' => 'ocean',
        'height' => 'lg',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('delaware-homes.jpg') : '',
        'parallax' => true,
        'overlay' => 'gradient',
        'alignment' => 'center',
        'headline' => 'What\'s Your Home Worth?',
        'subheadline' => 'Get Your Free Property Valuation Report',
        'content' => 'Discover your home\'s current market value with our comprehensive analysis. Our local experts combine market data, recent sales, and property insights to deliver an accurate valuation.',
        'content_width' => 'normal',
        'fade_in' => true,
        'buttons' => array(
            array(
                'text' => 'Get My Valuation',
                'url' => '#valuation-form',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-arrow-down',
                'icon_position' => 'right'
            )
        ),
        'section_id' => 'valuation-hero'
    ));
    
    // ============================================
    // Valuation Form Section
    // ============================================
    get_template_part('template-parts/sections/form', null, array(
        'style' => 'centered',
        'theme' => 'white',
        'padding' => 'xl',
        'form_id' => 'home-valuation-form',
        'headline' => 'Get Started in 60 Seconds',
        'subheadline' => 'Tell us about your property',
        'form_fields' => array(
            array(
                'type' => 'text',
                'name' => 'address',
                'label' => 'Property Address',
                'placeholder' => '123 Main Street',
                'required' => true,
                'width' => 'full'
            ),
            array(
                'type' => 'text',
                'name' => 'city',
                'label' => 'City',
                'placeholder' => 'Georgetown',
                'required' => true,
                'width' => 'third'
            ),
            array(
                'type' => 'select',
                'name' => 'state',
                'label' => 'State',
                'required' => true,
                'width' => 'third',
                'options' => array(
                    'DE' => 'Delaware',
                    'MD' => 'Maryland'
                )
            ),
            array(
                'type' => 'text',
                'name' => 'zip',
                'label' => 'ZIP Code',
                'placeholder' => '19947',
                'required' => true,
                'width' => 'third'
            ),
            array(
                'type' => 'select',
                'name' => 'bedrooms',
                'label' => 'Bedrooms',
                'required' => true,
                'width' => 'quarter',
                'options' => array(
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5+' => '5+'
                )
            ),
            array(
                'type' => 'select',
                'name' => 'bathrooms',
                'label' => 'Bathrooms',
                'required' => true,
                'width' => 'quarter',
                'options' => array(
                    '1' => '1',
                    '1.5' => '1.5',
                    '2' => '2',
                    '2.5' => '2.5',
                    '3+' => '3+'
                )
            ),
            array(
                'type' => 'text',
                'name' => 'square_feet',
                'label' => 'Approx. Sq Ft',
                'placeholder' => '2000',
                'required' => false,
                'width' => 'quarter'
            ),
            array(
                'type' => 'select',
                'name' => 'property_type',
                'label' => 'Property Type',
                'required' => true,
                'width' => 'quarter',
                'options' => array(
                    'single' => 'Single Family',
                    'townhome' => 'Townhome',
                    'condo' => 'Condo',
                    'land' => 'Land'
                )
            ),
            array(
                'type' => 'text',
                'name' => 'first_name',
                'label' => 'First Name',
                'required' => true,
                'width' => 'half'
            ),
            array(
                'type' => 'text',
                'name' => 'last_name',
                'label' => 'Last Name',
                'required' => true,
                'width' => 'half'
            ),
            array(
                'type' => 'email',
                'name' => 'email',
                'label' => 'Email Address',
                'required' => true,
                'width' => 'half'
            ),
            array(
                'type' => 'tel',
                'name' => 'phone',
                'label' => 'Phone Number',
                'required' => true,
                'width' => 'half'
            ),
            array(
                'type' => 'select',
                'name' => 'timeline',
                'label' => 'When are you thinking of selling?',
                'required' => false,
                'width' => 'full',
                'options' => array(
                    'asap' => 'ASAP',
                    '3months' => 'Within 3 months',
                    '6months' => 'Within 6 months',
                    'year' => 'Within a year',
                    'curious' => 'Just curious about value'
                )
            )
        ),
        'submit_text' => 'Get My Free Valuation',
        'section_id' => 'valuation-form'
    ));
    
    // ============================================
    // How It Works
    // ============================================
    get_template_part('template-parts/sections/process', null, array(
        'style' => 'icons-top',
        'theme' => 'light',
        'padding' => 'xl',
        'headline' => 'How Our Valuation Works',
        'subheadline' => 'A comprehensive approach to pricing your home',
        'columns' => 4,
        'steps' => array(
            array(
                'icon' => 'fas fa-home',
                'title' => 'Property Analysis',
                'description' => 'We analyze your property\'s unique features, upgrades, and condition.'
            ),
            array(
                'icon' => 'fas fa-chart-bar',
                'title' => 'Market Comparison',
                'description' => 'Review recent sales of similar homes in your neighborhood.'
            ),
            array(
                'icon' => 'fas fa-map-pin',
                'title' => 'Local Expertise',
                'description' => 'Our agents add insights about buyer demand and market trends.'
            ),
            array(
                'icon' => 'fas fa-file-alt',
                'title' => 'Your Report',
                'description' => 'Receive a detailed valuation report with pricing recommendations.'
            )
        ),
        'section_id' => 'valuation-process'
    ));
    
    // ============================================
    // Why Choose Parker Group
    // ============================================
    get_template_part('template-parts/sections/features', null, array(
        'style' => 'alternating',
        'theme' => 'white',
        'padding' => 'xl',
        'headline' => 'Why Sellers Choose The Parker Group',
        'subheadline' => 'Local expertise meets innovative marketing',
        'features' => array(
            array(
                'icon' => 'fas fa-trophy',
                'title' => 'Proven Results',
                'description' => 'We sell homes 51% faster than average and have closed over $922M in sales.',
                'image' => function_exists('hph_get_image_url') ? hph_get_image_url('results.jpg') : ''
            ),
            array(
                'icon' => 'fas fa-camera',
                'title' => 'Professional Marketing',
                'description' => 'HDR photography, 3D virtual tours, and targeted digital advertising showcase your home.',
                'image' => function_exists('hph_get_image_url') ? hph_get_image_url('marketing.jpg') : ''
            ),
            array(
                'icon' => 'fas fa-users',
                'title' => 'Team Advantage',
                'description' => '50+ agents working together means more exposure and faster sales for your home.',
                'image' => function_exists('hph_get_image_url') ? hph_get_image_url('team.jpg') : ''
            )
        ),
        'section_id' => 'why-parker'
    ));
    
    // ============================================
    // Market Stats
    // ============================================
    get_template_part('template-parts/sections/stats', null, array(
        'style' => 'counters',
        'theme' => 'ocean',
        'padding' => 'lg',
        'headline' => 'Delaware Market Leaders',
        'text_color' => 'white',
        'stats' => array(
            array(
                'number' => '2,755',
                'label' => 'Homes Sold',
                'icon' => 'fas fa-home'
            ),
            array(
                'number' => '$922M',
                'label' => 'Total Volume',
                'icon' => 'fas fa-dollar-sign'
            ),
            array(
                'number' => '51%',
                'label' => 'Faster Sales',
                'icon' => 'fas fa-clock'
            ),
            array(
                'number' => '50+',
                'label' => 'Expert Agents',
                'icon' => 'fas fa-users'
            )
        ),
        'section_id' => 'market-stats'
    ));
    
    // ============================================
    // Download Seller Guide CTA
    // ============================================
    get_template_part('template-parts/sections/cta', null, array(
        'style' => 'split',
        'theme' => 'light',
        'padding' => 'xl',
        'layout' => 'content-left',
        'headline' => 'Planning to Sell?',
        'subheadline' => 'Get The Keys to Close',
        'content' => 'Download our comprehensive seller\'s guide with everything you need to know about selling your home, from preparation to closing day.',
        'buttons' => array(
            array(
                'text' => 'Download Seller Guide',
                'url' => '/sellers-guide',
                'style' => 'primary',
                'size' => 'l',
                'icon' => 'fas fa-download',
                'icon_position' => 'right'
            )
        ),
        'image' => function_exists('hph_get_image_url') ? hph_get_image_url('keys-to-close.jpg') : '',
        'section_id' => 'seller-guide-cta'
    ));
    
    // ============================================
    // Final CTA
    // ============================================
    get_template_part('template-parts/sections/cta', null, array(
        'style' => 'centered',
        'theme' => 'gradient',
        'padding' => 'xl',
        'headline' => 'Ready to Know Your Home\'s Value?',
        'subheadline' => 'Get your free property valuation report today',
        'buttons' => array(
            array(
                'text' => 'Get My Valuation',
                'url' => '#valuation-form',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-arrow-up',
                'icon_position' => 'right'
            ),
            array(
                'text' => 'Call: 302.217.6692',
                'url' => 'tel:3022176692',
                'style' => 'outline-white',
                'size' => 'xl',
                'icon' => 'fas fa-phone',
                'icon_position' => 'left'
            )
        ),
        'section_id' => 'valuation-cta'
    ));
    ?>
    
</main>

<?php get_footer(); ?>