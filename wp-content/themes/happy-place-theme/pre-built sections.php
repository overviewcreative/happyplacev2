    <?php
    // ============================================
    // Our Services Section
    // ============================================
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'grid',
        'background' => 'light',
        'padding' => 'xl',
        'columns' => 3,
        'badge' => 'Our Services',
        'headline' => 'Complete Real Estate Solutions',
        'subheadline' => 'From first-time buyers to luxury estates, we handle every type of transaction',
        'animation' => true,
        'items' => array(
            array(
                'icon' => 'fas fa-key',
                'title' => 'Home Buying',
                'content' => 'Expert guidance through the entire purchase process, from pre-approval to closing. We help first-time and seasoned buyers find their perfect home.',
                'link' => array(
                    'text' => 'Start Buying',
                    'url' => '/buyers/'
                )
            ),
            array(
                'icon' => 'fas fa-home',
                'title' => 'Home Selling',
                'content' => 'Strategic marketing and pricing to sell your home quickly and for maximum value. Professional staging advice and comprehensive market analysis.',
                'link' => array(
                    'text' => 'Start Selling',
                    'url' => '/sellers/'
                )
            ),
            array(
                'icon' => 'fas fa-chart-line',
                'title' => 'Investment Properties',
                'content' => 'Identify profitable rental properties and vacation homes. Market analysis, cash flow projections, and investment strategy consulting.',
                'link' => array(
                    'text' => 'View Investments',
                    'url' => '/listing/'
                )
            )
        ),
        'section_id' => 'services-grid'
    ));
    ?>

    <?php
    // ============================================
    // Community Knowledge with Scattered Collage
    // ============================================
    get_template_part('template-parts/sections/content-photo-collage', null, array(
        'layout' => 'collage-right',
        'background' => 'white',
        'padding' => 'xl',
        'alignment' => 'left',
        'collage_style' => 'scattered',
        'badge' => 'Community Focus',
        'headline' => 'Delaware Beach Communities Expert',
        'subheadline' => 'Specialized knowledge of coastal and inland Delaware markets',
        'content' => 'From Rehoboth Beach to Dover, from Lewes to Middletown, we know Delaware inside and out. Our expertise extends to unique aspects of coastal living, flood zones, rental regulations, and seasonal market dynamics.<br><br>Whether you\'re looking for a beach house, family home, or investment property, we understand the nuances that make each Delaware community special.',
        'photos' => array(
            array(
                'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 7.jpg',
                'alt' => 'Waterfront property'
            ),
            array(
                'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 18.jpg',
                'alt' => 'Coastal home deck'
            ),
            array(
                'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 31.jpg',
                'alt' => 'Beach house exterior'
            ),
            array(
                'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 42.jpg',
                'alt' => 'Ocean view from home'
            )
        ),
        'buttons' => array(
            array(
                'text' => 'Explore Communities',
                'url' => '/communities/',
                'style' => 'primary',
                'size' => 'lg',
                'icon' => 'fas fa-map-marked-alt'
            )
        ),
        'animation' => true,
        'hover_effects' => true,
        'section_id' => 'community-knowledge'
    ));
    ?>

    <?php
    // ============================================
    // Market Performance Stats Section
    // ============================================
    get_template_part('template-parts/sections/stats', null, array(
        'style' => 'minimal',
        'theme' => 'light',
        'layout' => 'inline',
        'padding' => 'lg',
        'background' => 'light',
        'badge' => 'Market Performance',
        'headline' => '2024 Market Highlights',
        'subheadline' => 'Current Delaware real estate trends',
        'stats' => array(
            array(
                'number' => '15',
                'suffix' => ' Days',
                'label' => 'Avg. Time on Market',
                'icon' => 'fas fa-calendar-alt',
                'description' => 'Faster than state average',
                'trend' => 'down',
                'trend_value' => '-5 days'
            ),
            array(
                'number' => '105',
                'suffix' => '%',
                'label' => 'Of Asking Price',
                'icon' => 'fas fa-percentage',
                'description' => 'Average sale ratio',
                'trend' => 'up',
                'trend_value' => '+2%'
            ),
            array(
                'number' => '425',
                'prefix' => '$',
                'suffix' => 'K',
                'label' => 'Median Home Price',
                'icon' => 'fas fa-dollar-sign',
                'description' => 'Sussex County',
                'trend' => 'up',
                'trend_value' => '+12%'
            )
        ),
        'animate_counters' => true,
        'section_id' => 'market-stats'
    ));
    ?>

    <?php
    // ============================================
    // Testimonials Section
    // ============================================
    get_template_part('template-parts/sections/testimonials', null, array(
        'background' => 'primary',
        'padding' => 'xl',
        'badge' => 'Client Testimonials',
        'headline' => 'What Our Clients Say',
        'subheadline' => 'Real stories from real clients who found their happy place with us',
        'testimonials_count' => 3,
        'layout' => 'slider',
        'show_ratings' => true,
        'auto_play' => true,
        'section_id' => 'testimonials-home'
    ));
    ?>

    <?php
    // ============================================
    // FAQ Section
    // ============================================
    get_template_part('template-parts/sections/faq', null, array(
        'background' => 'light',
        'padding' => 'xl',
        'badge' => 'Frequently Asked Questions',
        'headline' => 'Got Questions? We Have Answers',
        'subheadline' => 'Find answers to the most common real estate questions',
        'layout' => 'accordion',
        'columns' => 1,
        'faqs' => array(
            array(
                'question' => 'How do I get started with buying a home?',
                'answer' => 'Start by getting pre-approved for a mortgage, then contact one of our expert agents who will help you understand your options and begin your property search based on your budget and preferences.'
            ),
            array(
                'question' => 'What should I expect during the home buying process?',
                'answer' => 'The process typically includes pre-approval, property search, making an offer, home inspection, finalizing financing, and closing. Our agents guide you through each step and keep you informed throughout the entire process.'
            ),
            array(
                'question' => 'How is the value of my home determined?',
                'answer' => 'Home value is determined by factors including location, size, condition, recent comparable sales, and current market conditions. Our agents provide detailed market analysis to help you understand your property\'s value.'
            ),
            array(
                'question' => 'What are the costs involved in buying or selling?',
                'answer' => 'Costs vary but typically include agent commissions, closing costs, inspections, and various fees. We provide detailed cost breakdowns upfront so you know exactly what to expect throughout the transaction.'
            ),
            array(
                'question' => 'How long does it typically take to buy or sell a home?',
                'answer' => 'Timeline varies based on market conditions, but typically 30-60 days from accepted offer to closing for purchases, and 30-90 days to sell depending on pricing and market conditions in your area.'
            )
        ),
        'section_id' => 'faq-home'
    ));
    ?>

    <?php
    // ============================================
    // Gallery Section - Recent Projects Masonry
    // ============================================
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'masonry',
        'background' => 'white',
        'padding' => 'xl',
        'alignment' => 'center',
        'badge' => 'Recent Projects',
        'headline' => 'Our Latest Work',
        'subheadline' => 'Beautiful homes and successful closings from across Delaware',
        'content' => 'Take a look at some of our recent projects - from beachfront properties to family homes, each one represents a happy family finding their perfect place.',
        'image_style' => 'default',
        'image_size' => 'medium',
        'masonry_columns' => 3,
        'gallery_gap' => 'lg',
        'animation' => true,
        'images' => array(
            array(
                'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 2.jpg',
                'alt' => 'Luxury waterfront home exterior',
                'caption' => 'Sold - Mariners Road Waterfront Estate'
            ),
            array(
                'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 7.jpg',
                'alt' => 'Modern kitchen with waterfront views',
                'caption' => 'Gourmet Kitchen with Water Views'
            ),
            array(
                'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 18.jpg',
                'alt' => 'Spacious deck overlooking water',
                'caption' => 'Entertaining Deck with Panoramic Views'
            ),
            array(
                'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 31.jpg',
                'alt' => 'Elegant master suite',
                'caption' => 'Master Suite Retreat'
            ),
            array(
                'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 42.jpg',
                'alt' => 'Waterfront property at sunset',
                'caption' => 'Golden Hour at the Water'
            ),
            array(
                'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 1.jpg',
                'alt' => 'Beautiful home entrance',
                'caption' => 'Welcoming Front Entrance'
            )
        ),
        'buttons' => array(
            array(
                'text' => 'View All Properties',
                'url' => '/listing/',
                'style' => 'primary',
                'size' => 'lg',
                'icon' => 'fas fa-images'
            ),
            array(
                'text' => 'Schedule Showing',
                'url' => '/contact/',
                'style' => 'outline',
                'size' => 'lg',
                'icon' => 'fas fa-calendar-check'
            )
        ),
        'section_id' => 'recent-projects-gallery'
    ));
    ?>

    <?php
    // ============================================
    // Meet Our Team - Agent Carousel
    // ============================================
    get_template_part('template-parts/sections/post-carousel', null, array(
        'post_type' => 'agent',
        'posts_per_page' => 4,
        'background' => 'dark',
        'height' => '60vh',
        'autoplay' => true,
        'autoplay_speed' => 7000,
        'show_dots' => true,
        'show_arrows' => true,
        'overlay' => true,
        'orderby' => 'date',
        'order' => 'DESC',
        'section_id' => 'team-carousel'
    ));
    ?>

    <?php
    // ============================================
    // Our Success Stories - Centered Collage
    // ============================================
    get_template_part('template-parts/sections/content-photo-collage', null, array(
        'layout' => 'collage-centered',
        'background' => 'white',
        'padding' => 'xl',
        'alignment' => 'center',
        'collage_style' => 'stacked',
        'badge' => 'Success Stories',
        'headline' => 'Celebrating Happy Homeowners',
        'subheadline' => 'Real homes, real families, real success stories',
        'content' => 'Every home has a story, and we\'re honored to be part of yours. From first-time buyers to luxury estates, from downsizing to dream homes, we celebrate every successful closing and the families who make these houses their happy place.',
        'photos' => array(
            array(
                'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 1.jpg',
                'alt' => 'Happy family home'
            ),
            array(
                'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 14.jpg',
                'alt' => 'Successful closing'
            ),
            array(
                'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 26.jpg',
                'alt' => 'New homeowners'
            ),
            array(
                'url' => get_template_directory_uri() . '/assets/images/26590 Mariners Rd. 38.jpg',
                'alt' => 'Dream home achieved'
            )
        ),
        'buttons' => array(
            array(
                'text' => 'Share Your Story',
                'url' => '/contact/',
                'style' => 'primary',
                'size' => 'lg',
                'icon' => 'fas fa-heart'
            ),
            array(
                'text' => 'View Testimonials',
                'url' => '#testimonials-home',
                'style' => 'outline',
                'size' => 'lg'
            )
        ),
        'animation' => true,
        'hover_effects' => true,
        'section_id' => 'success-stories'
    ));
    ?>

        <?php
    // ============================================
    // Featured Listings Carousel - Property Showcase
    // ============================================
    get_template_part('template-parts/sections/post-carousel', null, array(
        'post_type' => 'listing',
        'posts_per_page' => 6,
        'background' => 'dark',
        'height' => '70vh',
        'autoplay' => true,
        'autoplay_speed' => 6000,
        'show_dots' => true,
        'show_arrows' => true,
        'overlay' => true,
        'orderby' => 'date',
        'order' => 'DESC',
        'section_id' => 'featured-listings-carousel'
    ));
    ?>

    <?php
    // ============================================
    // Fallback: Latest Posts Carousel (if no listings)
    // ============================================
    get_template_part('template-parts/sections/post-carousel', null, array(
        'post_type' => 'post',
        'posts_per_page' => 5,
        'background' => 'light',
        'height' => '60vh',
        'autoplay' => true,
        'autoplay_speed' => 5000,
        'show_dots' => true,
        'show_arrows' => true,
        'overlay' => true,
        'orderby' => 'date',
        'order' => 'DESC',
        'section_id' => 'latest-posts-carousel'
    ));
    ?>

    <?php
    // ============================================
    // TEST CAROUSEL - Remove after debugging
    // ============================================
    ?>
    <div style="padding: 2rem; background: #f0f0f0; text-align: center;">
        <h2 style="margin-bottom: 1rem;">🔧 Test Carousel (Remove After Testing)</h2>
        <p style="margin-bottom: 2rem;">This test carousel should work regardless of your content.</p>
        <?php get_template_part('template-parts/sections/test-carousel'); ?>
    </div>
    <?php

    ?>
