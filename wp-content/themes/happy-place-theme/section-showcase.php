<?php
    // ============================================
    // 3. PARALLAX HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'image',
        'height' => 'xl',
        'is_top_of_page' => true,
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 10.jpg') : '',
        'parallax' => true,
        'overlay' => 'dark',
        'overlay_opacity' => '30',
        'alignment' => 'center',
        'headline' => 'Parallax Scrolling Effect',
        'subheadline' => 'Add depth with parallax backgrounds',
        'content' => 'Create an immersive experience with parallax scrolling that adds depth and engagement to your hero section.',
        'buttons' => array(
            array(
                'text' => 'Experience Demo',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl'
            )
        ),
        'section_id' => 'hero-parallax'
    ));
    ?>

    <!-- ============================================
         CONTENT SECTION VARIATIONS
         ============================================ -->
    
    <h1 style="text-align: center; padding: var(--hph-padding-3xl) 0; background: var(--hph-gray-100); margin: 0;">Content Section Variations</h1>
    
    <?php
    // 1. CENTERED CONTENT
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'centered',
        'background' => 'white',
        'padding' => 'xl',
        'alignment' => 'center',
        'badge' => 'Featured',
        'headline' => 'Centered Content Layout',
        'subheadline' => 'Perfect for focused messaging',
        'content' => 'This centered layout creates a strong focal point for your content. It\'s ideal for landing pages, about sections, or any content that needs to command attention.',
        'buttons' => array(
            array(
                'text' => 'Learn More',
                'url' => '#',
                'style' => 'primary',
                'size' => 'lg',
                'icon' => 'fas fa-arrow-right'
            )
        ),
        'animation' => true,
        'section_id' => 'content-centered'
    ));
    ?>
    
    <?php
    // 2. LEFT IMAGE CONTENT
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'left-image',
        'background' => 'light',
        'padding' => 'xl',
        'alignment' => 'left',
        'image' => array(
            'url' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 2.jpg') : '',
            'alt' => 'Modern home interior',
            'caption' => 'Beautiful imagery enhances your message'
        ),
        'headline' => 'Content with Left Image',
        'subheadline' => 'Visual storytelling at its best',
        'content' => 'Combine compelling visuals with your message. This layout places the image on the left, creating a natural reading flow from visual to text.',
        'buttons' => array(
            array(
                'text' => 'Explore Features',
                'url' => '#',
                'style' => 'primary',
                'icon' => 'fas fa-compass'
            ),
            array(
                'text' => 'View Gallery',
                'url' => '#',
                'style' => 'outline'
            )
        ),
        'animation' => true,
        'section_id' => 'content-left-image'
    ));
    ?>
    
    <?php
    // 3. RIGHT IMAGE CONTENT
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'right-image',
        'background' => 'white',
        'padding' => 'xl',
        'alignment' => 'left',
        'image' => array(
            'url' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 3.jpg') : '',
            'alt' => 'Cozy cabin exterior'
        ),
        'headline' => 'Content with Right Image',
        'subheadline' => 'Reversed layout for variety',
        'content' => 'Switch things up with the image on the right. This creates visual variety when you have multiple content sections on a page.',
        'buttons' => array(
            array(
                'text' => 'Get Started',
                'url' => '#',
                'style' => 'primary',
                'size' => 'lg'
            )
        ),
        'section_id' => 'content-right-image'
    ));
    ?>
    
    <?php
    // 4. GRID LAYOUT WITH ITEMS
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'grid',
        'background' => 'light',
        'padding' => 'xl',
        'columns' => 3,
        'headline' => 'Grid Layout Features',
        'subheadline' => 'Showcase multiple items elegantly',
        'items' => array(
            array(
                'icon' => 'fas fa-rocket',
                'title' => 'Fast Performance',
                'content' => 'Optimized for speed and efficiency, delivering lightning-fast experiences.',
                'link' => array(
                    'url' => '#',
                    'text' => 'Learn More'
                )
            ),
            array(
                'icon' => 'fas fa-shield-alt',
                'title' => 'Secure & Reliable',
                'content' => 'Built with security in mind, ensuring your data is always protected.',
                'link' => array(
                    'url' => '#',
                    'text' => 'Security Features'
                )
            ),
            array(
                'icon' => 'fas fa-chart-line',
                'title' => 'Analytics Dashboard',
                'content' => 'Comprehensive insights and reporting to track your success.',
                'link' => array(
                    'url' => '#',
                    'text' => 'View Demo'
                )
            ),
            array(
                'icon' => 'fas fa-users',
                'title' => 'Team Collaboration',
                'content' => 'Work together seamlessly with built-in collaboration tools.',
                'link' => array(
                    'url' => '#',
                    'text' => 'Features'
                )
            ),
            array(
                'icon' => 'fas fa-mobile-alt',
                'title' => 'Mobile Ready',
                'content' => 'Fully responsive design that works perfectly on all devices.',
                'link' => array(
                    'url' => '#',
                    'text' => 'Mobile Features'
                )
            ),
            array(
                'icon' => 'fas fa-cog',
                'title' => 'Easy Setup',
                'content' => 'Get started in minutes with our intuitive setup process.',
                'link' => array(
                    'url' => '#',
                    'text' => 'Get Started'
                )
            )
        ),
        'animation' => true,
        'section_id' => 'content-grid'
    ));
    ?>
    
    <?php
    // 5. STATS LAYOUT
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'stats',
        'background' => 'primary',
        'padding' => 'xl',
        'headline' => 'Impressive Statistics',
        'subheadline' => 'Numbers that speak for themselves',
        'stats' => array(
            array(
                'number' => '10K',
                'suffix' => '+',
                'label' => 'Happy Customers',
                'description' => 'Trusted worldwide'
            ),
            array(
                'number' => '99.9',
                'suffix' => '%',
                'label' => 'Uptime',
                'description' => 'Always available'
            ),
            array(
                'number' => '24/7',
                'label' => 'Support',
                'description' => 'We\'re here to help'
            ),
            array(
                'number' => '500',
                'suffix' => '+',
                'label' => 'Properties',
                'description' => 'In our portfolio'
            )
        ),
        'animation' => true,
        'section_id' => 'content-stats'
    ));
    ?>
    
    <!-- ============================================
         CTA SECTION VARIATIONS
         ============================================ -->
    
    <h1 style="text-align: center; padding: var(--hph-padding-3xl) 0; background: var(--hph-gray-100); margin: 0;">CTA Section Variations</h1>
    
    <?php
    // 6. CENTERED CTA
    get_template_part('template-parts/sections/cta', null, array(
        'layout' => 'centered',
        'background' => 'gradient',
        'padding' => 'xl',
        'alignment' => 'center',
        'badge' => 'Limited Time',
        'headline' => 'Ready to Transform Your Business?',
        'subheadline' => 'Join thousands of satisfied customers',
        'content' => 'Start your journey today with our powerful platform. No credit card required.',
        'buttons' => array(
            array(
                'text' => 'Start Free Trial',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-play'
            ),
            array(
                'text' => 'Watch Demo',
                'url' => '#',
                'style' => 'outline-white',
                'size' => 'xl',
                'icon' => 'fas fa-video'
            )
        ),
        'animation' => true,
        'section_id' => 'cta-centered'
    ));
    ?>
    
    <?php
    // 7. SPLIT CTA WITH FORM
    get_template_part('template-parts/sections/cta', null, array(
        'layout' => 'split',
        'background' => 'dark',
        'padding' => 'xl',
        'alignment' => 'left',
        'headline' => 'Get Early Access',
        'subheadline' => 'Be the first to know when we launch',
        'content' => 'Join our exclusive waitlist and receive special launch pricing.',
        'form' => array(
            'title' => 'Sign Up Now',
            'button_text' => 'Join Waitlist'
        ),
        'animation' => true,
        'section_id' => 'cta-split-form'
    ));
    ?>
    
    <?php
    // 8. BOXED CTA
    get_template_part('template-parts/sections/cta', null, array(
        'layout' => 'boxed',
        'background' => 'light',
        'padding' => '2xl',
        'alignment' => 'center',
        'badge' => 'New',
        'headline' => 'Upgrade Your Plan',
        'subheadline' => 'Unlock premium features',
        'content' => 'Get access to advanced features, priority support, and unlimited resources.',
        'buttons' => array(
            array(
                'text' => 'View Pricing',
                'url' => '#',
                'style' => 'primary',
                'size' => 'xl',
                'icon' => 'fas fa-tag'
            ),
            array(
                'text' => 'Compare Plans',
                'url' => '#',
                'style' => 'outline',
                'size' => 'xl'
            )
        ),
        'section_id' => 'cta-boxed'
    ));
    ?>
    
    <?php
    // 9. IMAGE BACKGROUND CTA
    get_template_part('template-parts/sections/cta', null, array(
        'layout' => 'centered',
        'background' => 'image',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 4.jpg') : '',
        'overlay' => true,
        'overlay_opacity' => '60',
        'padding' => '2xl',
        'alignment' => 'center',
        'headline' => 'Discover Your Dream Home',
        'subheadline' => 'Exclusive properties in prime locations',
        'buttons' => array(
            array(
                'text' => 'Browse Properties',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-home'
            )
        ),
        'animation' => true,
        'section_id' => 'cta-image'
    ));
    ?>
    
    <?php
    // 10. INLINE CTA
    get_template_part('template-parts/sections/cta', null, array(
        'layout' => 'inline',
        'background' => 'primary',
        'padding' => 'lg',
        'alignment' => 'left',
        'headline' => 'Need Help Getting Started?',
        'content' => 'Our team is here to help you every step of the way.',
        'buttons' => array(
            array(
                'text' => 'Contact Support',
                'url' => '#',
                'style' => 'white',
                'size' => 'lg',
                'icon' => 'fas fa-headset'
            )
        ),
        'section_id' => 'cta-inline'
    ));
    ?>
    
    <!-- ============================================
         FEATURES SECTION VARIATIONS
         ============================================ -->
    
    <h1 style="text-align: center; padding: var(--hph-padding-3xl) 0; background: var(--hph-gray-100); margin: 0;">Features Section Variations</h1>
    
    <?php
    // 11. GRID FEATURES
    get_template_part('template-parts/sections/features', null, array(
        'layout' => 'grid',
        'background' => 'white',
        'padding' => 'xl',
        'columns' => 3,
        'alignment' => 'center',
        'badge' => 'Features',
        'headline' => 'Everything You Need',
        'subheadline' => 'Powerful features to help you succeed',
        'features' => array(
            array(
                'icon' => 'fas fa-bolt',
                'title' => 'Lightning Fast',
                'content' => 'Optimized performance ensures your site loads in milliseconds.',
                'link' => array(
                    'url' => '#',
                    'text' => 'Learn More'
                )
            ),
            array(
                'icon' => 'fas fa-lock',
                'title' => 'Secure by Default',
                'content' => 'Enterprise-grade security keeps your data safe.',
                'link' => array(
                    'url' => '#',
                    'text' => 'Security Info'
                )
            ),
            array(
                'icon' => 'fas fa-globe',
                'title' => 'Global CDN',
                'content' => 'Content delivered from servers worldwide for optimal speed.',
                'link' => array(
                    'url' => '#',
                    'text' => 'View Locations'
                )
            )
        ),
        'icon_style' => 'circle',
        'animation' => true,
        'section_id' => 'features-grid'
    ));
    ?>
    
    <!-- ============================================
         AGENTS SECTION VARIATIONS
         ============================================ -->
    
    <h1 style="text-align: center; padding: var(--hph-padding-3xl) 0; background: var(--hph-gray-100); margin: 0;">Agent Showcase Variations</h1>
    
    <?php
    // 18. AGENTS GRID LAYOUT
    get_template_part('template-parts/sections/agents-loop', null, array(
        'layout' => 'grid',
        'columns' => 5,
        'background' => 'white',
        'padding' => 'xl',
        'headline' => 'Meet Our Expert Team',
        'subheadline' => 'Dedicated professionals ready to help you find your dream home',
        'show_bio' => true,
        'show_contact' => true,
        'show_social' => true,
        'show_stats' => false,
        'show_button' => true,
        'animation' => true,
        'section_id' => 'agents-grid'
    ));
    ?>
    
    <!-- ============================================
         LISTINGS SECTION VARIATIONS
         ============================================ -->
    
    <h1 style="text-align: center; padding: var(--hph-padding-3xl) 0; background: var(--hph-gray-100); margin: 0;">Property Listings Showcase</h1>
    
    <?php
    // 19. FEATURED LISTINGS LAYOUT
    get_template_part('template-parts/sections/listings-loop', null, array(
        'layout' => 'featured',
        'background' => 'white',
        'padding' => 'xl',
        'headline' => 'Featured Properties',
        'subheadline' => 'Discover our handpicked selection of exceptional homes',
        'listings' => array(), // Force demo data for showcase - will fallback gracefully
        'show_price' => true,
        'show_status' => true,
        'show_details' => true,
        'show_agent' => false,
        'show_favorite' => true,
        'show_compare' => false,
        'image_aspect' => '4:3',
        'animation' => true,
        'section_id' => 'listings-featured'
    ));
    
    // 20. GRID LISTINGS WITH FILTERS
    get_template_part('template-parts/sections/listings-loop', null, array(
        'layout' => 'grid',
        'columns' => 3,
        'background' => 'light',
        'padding' => 'xl',
        'headline' => 'Search Properties',
        'subheadline' => 'Find your perfect home with our advanced search filters',
        'listings' => array(), // Force demo data to prevent queries
        'show_price' => true,
        'show_status' => true,
        'show_details' => true,
        'show_agent' => true,
        'show_favorite' => true,
        'show_compare' => true,
        'image_aspect' => '4:3',
        'animation' => true,
        'filters' => true,
        'section_id' => 'listings-grid-filters'
    ));
    
    // 21. LIST VIEW PROPERTIES
    get_template_part('template-parts/sections/listings-loop', null, array(
        'layout' => 'list',
        'background' => 'white',
        'padding' => 'xl',
        'content_width' => 'wide',
        'headline' => 'Detailed Property View',
        'subheadline' => 'Comprehensive information at a glance with our list layout',
        'listings' => array(), // Force demo data to prevent queries
        'show_price' => true,
        'show_status' => true,
        'show_details' => true,
        'show_agent' => false,
        'show_favorite' => true,
        'show_compare' => true,
        'image_aspect' => '4:3',
        'animation' => true,
        'section_id' => 'listings-list'
    ));
    
    // 22. LUXURY PROPERTIES - Dark Theme
    get_template_part('template-parts/sections/listings-loop', null, array(
        'layout' => 'grid',
        'columns' => 2,
        'background' => 'dark',
        'padding' => 'xl',
        'headline' => 'Luxury Collection',
        'subheadline' => 'Exclusive properties for the most discerning buyers',
        'listings' => array(), // Force demo data to prevent queries
        'show_price' => true,
        'show_status' => false,
        'show_details' => true,
        'show_agent' => true,
        'show_favorite' => true,
        'show_compare' => false,
        'image_aspect' => '16:9',
        'animation' => true,
        'section_id' => 'listings-luxury'
    ));
    
    // 23. COMPACT GRID - 4 Columns
    get_template_part('template-parts/sections/listings-loop', null, array(
        'layout' => 'grid',
        'columns' => 4,
        'background' => 'light',
        'padding' => 'lg',
        'headline' => 'Quick Browse',
        'subheadline' => 'Browse more properties in our compact grid view',
        'listings' => array(), // Force demo data to prevent queries
        'show_price' => true,
        'show_status' => true,
        'show_details' => false,
        'show_agent' => false,
        'show_favorite' => true,
        'show_compare' => false,
        'image_aspect' => 'square',
        'animation' => true,
        'section_id' => 'listings-compact'
    ));
    
    // 24. GRADIENT BACKGROUND SHOWCASE
    get_template_part('template-parts/sections/listings-loop', null, array(
        'layout' => 'grid',
        'columns' => 3,
        'background' => 'gradient',
        'padding' => '2xl',
        'headline' => 'Premium Showcase',
        'subheadline' => 'Presenting our most sought-after properties',
        'listings' => array(), // Force demo data to prevent queries
        'show_price' => true,
        'show_status' => true,
        'show_details' => true,
        'show_agent' => false,
        'show_favorite' => true,
        'show_compare' => false,
        'image_aspect' => '4:3',
        'animation' => true,
        'section_id' => 'listings-gradient'
    ));
    
    // 25. MINIMAL NO-FRILLS VIEW
    get_template_part('template-parts/sections/listings-loop', null, array(
        'layout' => 'grid',
        'columns' => 3,
        'background' => 'white',
        'padding' => 'md',
        'headline' => 'Clean & Simple',
        'subheadline' => '',
        'listings' => array(), // Force demo data to prevent queries
        'show_price' => true,
        'show_status' => false,
        'show_details' => false,
        'show_agent' => false,
        'show_favorite' => false,
        'show_compare' => false,
        'image_aspect' => '4:3',
        'animation' => false,
        'filters' => false,
        'section_id' => 'listings-minimal'
    ));
    ?>

</div>


    <?php
    // ============================================
    // 3. IMAGE HERO - With Dark Overlay
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'image',
        'height' => 'xl',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('hero-bg.jpg') : '',
        'overlay' => 'dark',
        'overlay_opacity' => '40',
        'alignment' => 'center',
        'headline' => 'Stunning Image Backgrounds',
        'subheadline' => 'Your content over beautiful imagery',
        'content' => 'Combine high-quality images with overlay effects to create depth and ensure text readability.',
        'buttons' => array(
            array(
                'text' => 'Browse Gallery',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-images',
                'icon_position' => 'right'
            )
        ),
        'scroll_indicator' => true,
        'fade_in' => true,
        'section_id' => 'hero-image-dark'
    ));
    ?>

    <?php
    // ============================================
    // 4. IMAGE HERO - With Light Overlay
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'image',
        'height' => 'lg',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 1.jpg') : '',
        'overlay' => 'light',
        'overlay_opacity' => '60',
        'alignment' => 'right',
        'headline' => 'Light Overlay Option',
        'subheadline' => 'Perfect for darker images',
        'content' => 'When your background image is dark, use a light overlay to maintain contrast and readability.',
        'buttons' => array(
            array(
                'text' => 'Contact Us',
                'url' => '#',
                'style' => 'primary',
                'size' => 'xl'
            )
        ),
        'section_id' => 'hero-image-light'
    ));
    ?>

    <?php
    // ============================================
    // 5. IMAGE HERO - With Gradient Overlay
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'image',
        'height' => 'lg',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 5.jpg') : '',
        'overlay' => 'gradient',
        'overlay_opacity' => '60',
        'alignment' => 'center',
        'content_width' => 'narrow',
        'badge' => 'Premium',
        'badge_icon' => 'fas fa-crown',
        'headline' => 'Gradient Overlay Effects',
        'subheadline' => 'Combine images with brand color gradients',
        'buttons' => array(
            array(
                'text' => 'Explore Premium',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl'
            )
        ),
        'section_id' => 'hero-gradient-overlay'
    ));
    ?>

    <?php
    // ============================================
    // 6. PARALLAX HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'image',
        'height' => 'xl',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 10.jpg') : '',
        'parallax' => true,
        'overlay' => 'dark',
        'overlay_opacity' => '30',
        'alignment' => 'center',
        'headline' => 'Parallax Scrolling Effect',
        'subheadline' => 'Add depth with parallax backgrounds',
        'content' => 'Create an immersive experience with parallax scrolling that adds depth and visual interest to your hero sections.',
        'buttons' => array(
            array(
                'text' => 'See It In Action',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-play',
                'icon_position' => 'left'
            )
        ),
        'scroll_indicator' => true,
        'fade_in' => true,
        'section_id' => 'hero-parallax'
    ));
    ?>

    <?php
    // ============================================
    // 7. VIDEO HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'video',
        'height' => 'full',
        'background_video' => get_template_directory_uri() . '/assets/videos/hero-video.mp4',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 15.jpg') : '', // Poster for mobile
        'overlay' => 'gradient-radial',
        'overlay_opacity' => '40',
        'alignment' => 'center',
        'headline' => 'Dynamic Video Backgrounds',
        'subheadline' => 'Bring your hero sections to life',
        'content' => 'Video backgrounds create engaging, dynamic experiences that capture attention and tell your story.',
        'buttons' => array(
            array(
                'text' => 'Watch Demo',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-play-circle',
                'icon_position' => 'left'
            ),
            array(
                'text' => 'Learn More',
                'url' => '#',
                'style' => 'outline-white',
                'size' => 'xl'
            )
        ),
        'scroll_indicator' => true,
        'fade_in' => true,
        'section_id' => 'hero-video'
    ));
    ?>

    <?php
    // ============================================
    // 8. SPLIT HERO - Half and Half
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'split',
        'height' => 'lg',
        'alignment' => 'left',
        'content_width' => 'normal',
        'headline' => 'Split Layout Design',
        'subheadline' => 'Content on one side, visuals on the other',
        'content' => 'Split hero sections create a balanced layout perfect for showcasing features alongside compelling visuals.',
        'buttons' => array(
            array(
                'text' => 'Start Free Trial',
                'url' => '#',
                'style' => 'primary',
                'size' => 'xl'
            ),
            array(
                'text' => 'View Pricing',
                'url' => '#',
                'style' => 'outline-white',
                'size' => 'xl'
            )
        ),
        'section_id' => 'hero-split'
    ));
    ?>

    <?php
    // ============================================
    // 9. PROPERTY HERO - Real Estate Specific
    // ============================================
    
    // Simulate a listing ID for demo (in production, this would be a real listing)
    $demo_listing_id = 123;
    
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'property',
        'height' => 'xl',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 20.jpg') : '',
        'overlay' => 'gradient',
        'overlay_opacity' => '30',
        'alignment' => 'left',
        'headline' => '123 Oceanview Drive', // Would be dynamic in production
        'subheadline' => 'Luxury Beachfront Estate in Malibu', // Would be dynamic
        'listing_id' => $demo_listing_id,
        'show_gallery' => true,
        'show_status' => true,
        'show_price' => true,
        'show_stats' => true,
        'buttons' => array(
            array(
                'text' => 'Schedule Tour',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-calendar',
                'icon_position' => 'left'
            ),
            array(
                'text' => 'Virtual Tour',
                'url' => '#',
                'style' => 'outline-white',
                'size' => 'xl',
                'icon' => 'fas fa-vr-cardboard',
                'icon_position' => 'left'
            )
        ),
        'scroll_indicator' => true,
        'fade_in' => true,
        'section_id' => 'hero-property'
    ));
    ?>

    <?php
    // ============================================
    // 10. SMALL HEIGHT HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'gradient',
        'height' => 'sm',
        'alignment' => 'center',
        'headline' => 'Compact Hero Section',
        'subheadline' => 'When you need less vertical space',
        'buttons' => array(
            array(
                'text' => 'Quick Action',
                'url' => '#',
                'style' => 'white',
                'size' => 'l'
            )
        ),
        'section_id' => 'hero-small'
    ));
    ?>

    <?php
    // ============================================
    // 11. FULL HEIGHT HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'image',
        'height' => 'full',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 25.jpg') : '',
        'overlay' => 'primary-gradient',
        'overlay_opacity' => '50',
        'alignment' => 'center',
        'badge' => 'Featured',
        'headline' => 'Full Viewport Height',
        'subheadline' => 'Maximum impact with full-screen heroes',
        'content' => 'Take over the entire viewport for a truly immersive first impression.',
        'buttons' => array(
            array(
                'text' => 'Dive In',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-arrow-down',
                'icon_position' => 'right'
            )
        ),
        'scroll_indicator' => true,
        'fade_in' => true,
        'section_id' => 'hero-full-height'
    ));
    ?>

    <?php
    // ============================================
    // 12. NARROW CONTENT HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'gradient',
        'height' => 'lg',
        'alignment' => 'center',
        'content_width' => 'narrow',
        'headline' => 'Focused Content Width',
        'subheadline' => 'Narrow container for better readability',
        'content' => 'Sometimes less is more. A narrow content width helps focus attention and improves readability for text-heavy hero sections.',
        'buttons' => array(
            array(
                'text' => 'Read More',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl'
            )
        ),
        'section_id' => 'hero-narrow'
    ));
    ?>

    <?php
    // ============================================
    // 13. WIDE CONTENT HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'image',
        'height' => 'lg',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 30.jpg') : '',
        'overlay' => 'dark',
        'overlay_opacity' => '50',
        'alignment' => 'center',
        'content_width' => 'wide',
        'headline' => 'Extended Content Width',
        'subheadline' => 'More space for complex layouts',
        'content' => 'Wide content containers give you more flexibility for complex hero layouts with multiple elements.',
        'buttons' => array(
            array(
                'text' => 'Explore Wide',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl'
            )
        ),
        'section_id' => 'hero-wide'
    ));
    ?>

    <?php
    // ============================================
    // 14. FULL WIDTH CONTENT HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'minimal',
        'height' => 'md',
        'alignment' => 'left',
        'content_width' => 'full',
        'headline' => 'Edge-to-Edge Content',
        'subheadline' => 'No container constraints',
        'content' => 'Full width heroes remove all padding for edge-to-edge designs.',
        'buttons' => array(
            array(
                'text' => 'Full Width Demo',
                'url' => '#',
                'style' => 'primary',
                'size' => 'xl'
            )
        ),
        'section_id' => 'hero-full-width'
    ));
    ?>

    <?php
    // ============================================
    // 15. LEFT ALIGNED HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'image',
        'height' => 'lg',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 35.jpg') : '',
        'overlay' => 'gradient',
        'overlay_opacity' => '40',
        'alignment' => 'left',
        'headline' => 'Left-Aligned Content',
        'subheadline' => 'Great for storytelling and progressive disclosure',
        'content' => 'Left alignment creates a natural reading flow and works well with split layouts.',
        'buttons' => array(
            array(
                'text' => 'Start Journey',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-arrow-right',
                'icon_position' => 'right'
            )
        ),
        'fade_in' => true,
        'section_id' => 'hero-left'
    ));
    ?>

    <?php
    // ============================================
    // 16. RIGHT ALIGNED HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'image',
        'height' => 'lg',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 40.jpg') : '',
        'overlay' => 'dark',
        'overlay_opacity' => '50',
        'alignment' => 'right',
        'headline' => 'Right-Aligned Layout',
        'subheadline' => 'Create visual balance with right alignment',
        'content' => 'Right alignment can create interesting compositions, especially when paired with left-heavy imagery.',
        'buttons' => array(
            array(
                'text' => 'Discover More',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl'
            )
        ),
        'section_id' => 'hero-right'
    ));
    ?>

    <?php
    // ============================================
    // 17. MULTIPLE BUTTONS HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'gradient',
        'height' => 'lg',
        'alignment' => 'center',
        'headline' => 'Multiple Call-to-Actions',
        'subheadline' => 'Give users options with multiple buttons',
        'content' => 'Sometimes you need to provide multiple pathways for different user intents.',
        'buttons' => array(
            array(
                'text' => 'Primary Action',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-rocket',
                'icon_position' => 'left'
            ),
            array(
                'text' => 'Secondary',
                'url' => '#',
                'style' => 'outline-white',
                'size' => 'xl'
            ),
            array(
                'text' => 'Learn More',
                'url' => '#',
                'style' => 'primary',
                'size' => 'l',
                'icon' => 'fas fa-info-circle',
                'icon_position' => 'left'
            )
        ),
        'section_id' => 'hero-multi-buttons'
    ));
    ?>

    <?php
    // ============================================
    // 18. ANIMATED HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'image',
        'height' => 'xl',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('hero.jpg') : '',
        'overlay' => 'gradient-radial',
        'overlay_opacity' => '40',
        'alignment' => 'center',
        'headline' => 'Animated Content Entry',
        'subheadline' => 'Smooth animations draw attention',
        'content' => 'Enable fade-in animations to create a more dynamic and engaging hero section.',
        'buttons' => array(
            array(
                'text' => 'See Animation',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl'
            )
        ),
        'scroll_indicator' => true,
        'fade_in' => true,
        'section_id' => 'hero-animated'
    ));
    ?>

    <?php
    // ============================================
    // 19. BADGE VARIATIONS HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'gradient',
        'height' => 'lg',
        'alignment' => 'center',
        'badge' => 'Limited Time Offer',
        'badge_icon' => 'fas fa-clock',
        'headline' => 'Hero with Badge Elements',
        'subheadline' => 'Add context with badge labels',
        'content' => 'Badges help communicate status, urgency, or category information at a glance.',
        'buttons' => array(
            array(
                'text' => 'Claim Offer',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-gift',
                'icon_position' => 'left'
            )
        ),
        'section_id' => 'hero-badge'
    ));
    ?>

    <?php
    // ============================================
    // 20. NO BUTTONS HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'minimal',
        'height' => 'md',
        'alignment' => 'center',
        'headline' => 'Simple Text-Only Hero',
        'subheadline' => 'Sometimes you don\'t need call-to-action buttons',
        'content' => 'A clean, text-only hero can be powerful for setting tone or providing context without pushing for immediate action. Perfect for blog posts, articles, or informational pages.',
        'section_id' => 'hero-no-buttons'
    ));
    ?>

    <?php
    // ============================================
    // 21. HEADLINE ONLY HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'gradient',
        'height' => 'sm',
        'alignment' => 'center',
        'headline' => 'Bold. Simple. Effective.',
        'section_id' => 'hero-headline-only'
    ));
    ?>

    <?php
    // ============================================
    // 22. COMPLEX CONTENT HERO
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'image',
        'height' => 'xl',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('26590 Mariners Rd. 43.jpg') : '',
        'overlay' => 'dark',
        'overlay_opacity' => '60',
        'alignment' => 'center',
        'badge' => 'Enterprise Solution',
        'badge_icon' => 'fas fa-building',
        'headline' => 'Complete Hero Configuration',
        'subheadline' => 'Every element working together in harmony',
        'content' => 'This hero demonstrates all available elements: badge with icon, headline, subheadline, content paragraph, multiple buttons with icons, scroll indicator, fade-in animations, and background with overlay. It\'s the complete package for maximum impact and functionality.',
        'buttons' => array(
            array(
                'text' => 'Get Started Now',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-rocket',
                'icon_position' => 'right',
                'target' => '_self'
            ),
            array(
                'text' => 'Download Brochure',
                'url' => '#',
                'style' => 'outline-white',
                'size' => 'xl',
                'icon' => 'fas fa-download',
                'icon_position' => 'left',
                'target' => '_blank'
            )
        ),
        'scroll_indicator' => true,
        'fade_in' => true,
        'parallax' => true,
        'section_id' => 'hero-complete'
    ));
    ?>

</div>

<!-- Sticky Navigation for Demo -->
<nav style="position: fixed; top: 50%; right: 20px; transform: translateY(-50%); z-index: 100; background: var(--hph-white); padding: var(--hph-space-6); border-radius: var(--hph-radius-lg); box-shadow: 0 10px 30px rgba(0,0,0,0.1); max-height: 80vh; overflow-y: auto; min-width: 180px;">
    <h3 style="font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold); margin-bottom: var(--hph-space-4); color: var(--hph-gray-900);">Section Navigation</h3>
    
    <div style="display: flex; flex-direction: column; gap: var(--hph-gap-xs);">
        <h4 style="font-size: var(--hph-text-xs); font-weight: var(--hph-font-semibold); color: var(--hph-gray-600); margin: var(--hph-space-2) 0;">Hero</h4>
        <a href="#hero-minimal" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Minimal</a>
        <a href="#hero-gradient" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Gradient</a>
        <a href="#hero-parallax" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Parallax</a>
        
        <h4 style="font-size: var(--hph-text-xs); font-weight: var(--hph-font-semibold); color: var(--hph-gray-600); margin: var(--hph-space-2) 0;">Content</h4>
        <a href="#content-centered" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Centered</a>
        <a href="#content-left-image" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Left Image</a>
        <a href="#content-right-image" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Right Image</a>
        <a href="#content-grid" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Grid</a>
        <a href="#content-stats" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Stats</a>
        
        <h4 style="font-size: var(--hph-text-xs); font-weight: var(--hph-font-semibold); color: var(--hph-gray-600); margin: var(--hph-space-2) 0;">CTA</h4>
        <a href="#cta-centered" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Centered</a>
        <a href="#cta-split" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Split</a>
        <a href="#cta-minimal" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Minimal</a>
        
        <h4 style="font-size: var(--hph-text-xs); font-weight: var(--hph-font-semibold); color: var(--hph-gray-600); margin: var(--hph-space-2) 0;">Features</h4>
        <a href="#features-cards" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Cards</a>
        <a href="#features-list" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">List</a>
        <a href="#features-grid" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Grid</a>
        
        <h4 style="font-size: var(--hph-text-xs); font-weight: var(--hph-font-semibold); color: var(--hph-gray-600); margin: var(--hph-space-2) 0;">Agents</h4>
        <a href="#agents-grid" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Team Grid</a>
        
        <h4 style="font-size: var(--hph-text-xs); font-weight: var(--hph-font-semibold); color: var(--hph-gray-600); margin: var(--hph-space-2) 0;">Listings</h4>
        <a href="#listings-featured" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Featured</a>
        <a href="#listings-grid-filters" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Grid + Filters</a>
        <a href="#listings-list" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">List View</a>
        <a href="#listings-luxury" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Luxury</a>
        <a href="#listings-compact" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Compact</a>
        <a href="#listings-gradient" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Gradient</a>
        <a href="#listings-minimal" style="font-size: var(--hph-text-xs); color: var(--hph-primary); text-decoration: none;">Minimal</a>
    </div>
</nav>
