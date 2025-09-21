<?php
/**
 * Template Name: Sellers Page
 * 
 * Comprehensive page for home sellers with relevant sections and information
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header();
?>

    <?php
    // ============================================
    // Hero Section - For Home Sellers
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'image',
        'theme' => 'primary',
        'height' => 'lg',
        'is_top_of_page' => true,
        'background_image' => hph_add_fastly_optimization(get_template_directory_uri() . '/assets/images/hero-bg4.jpg', 'full'),
        'ken_burns' => true,
        'ken_burns_direction' => 'zoom-out',
        'ken_burns_duration' => 35,
        'overlay' => 'dark-subtle',
        'alignment' => 'left',
        'headline' => 'The Keys to Close.',
        'subheadline' => "Selling a home is a big deal, but it shouldn't be difficult. From pre-list to close, we have you covered.",
        'buttons' => array(
            array(
                'text' => 'Get Home Value',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-calculator',
                'icon_position' => 'right',
                'data_attributes' => 'data-modal-form="fello-search-widget" data-modal-id="hph-fello-modal" data-modal-title="Search Homes" data-modal-subtitle="Find your perfect home with our advanced search tools."'
            ),
            array(
                'text' => 'Schedule Consultation',
                'url' => '#',
                'style' => 'outline-white',
                'size' => 'xl',
                'icon' => 'fas fa-calendar',
                'data_attributes' => 'data-modal-form="general-contact" data-modal-id="hph-consultation-modal" data-modal-title="Schedule Consultation" data-modal-subtitle="Let\'s discuss your real estate goals and how we can help."'
            )
        ),
        'section_id' => 'hero-sellers'
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
        'headline' => "Selling your home is one of life's most significant decisions.",
        'subheadline' => "We're truly honored you're considering The Parker Group.",
        'content' => "We believe selling your home should be an experience filled with confidence, clarity and genuine care. That's why we've build our entire approach around you--your needs, your timeline, and your dreams for what comes next.",
        'buttons' => array(
            array(
                'text' => 'Schedule Consultation',
                'url' => '#',
                'style' => 'primary',
                'icon' => 'fas fa-calendar',
                'data_attributes' => 'data-modal-form="general-contact" data-modal-id="hph-consultation-modal" data-modal-title="Schedule Consultation" data-modal-subtitle="Let\'s discuss your real estate goals and how we can help."'
            )
        ),
        'animation' => true,
        'section_id' => 'content-left-image'
    ));
    ?>

    <?php
    // ============================================
    // Features Section - Why Sell With Us
    // ============================================
    get_template_part('template-parts/sections/features', null, array(
        'layout' => 'grid',
        'background' => 'white',
        'padding' => 'xl',
        'columns' => 3,
        'badge' => 'Selling Advantages',
        'headline' => 'Maximize Your Home\'s Value',
        'subheadline' => 'Our comprehensive approach gets you more money in less time',
        'content' => 'We combine cutting-edge marketing technology with proven selling strategies to ensure your home stands out in the market.',
        'features' => array(
            array(
                'icon' => 'fas fa-dollar-sign',
                'title' => 'Premium Pricing Strategy',
                'content' => 'Advanced market analysis and pricing algorithms ensure your home is priced competitively to attract multiple offers while maximizing value.'
            ),
            array(
                'icon' => 'fas fa-camera-retro',
                'title' => 'Professional Photography',
                'content' => 'High-quality photos, virtual tours, and drone footage showcase your property in the best possible light across all marketing channels.'
            ),
            array(
                'icon' => 'fas fa-megaphone',
                'title' => 'Comprehensive Marketing',
                'content' => 'Multi-channel marketing including MLS, major real estate websites, social media advertising, and targeted email campaigns to qualified buyers.'
            ),
            array(
                'icon' => 'fas fa-home',
                'title' => 'Staging Consultation',
                'content' => 'Professional staging advice and recommendations to make your home more appealing to buyers and justify premium pricing.'
            ),
            array(
                'icon' => 'fas fa-chart-bar',
                'title' => 'Market Performance Tracking',
                'content' => 'Weekly reports on showing activity, buyer feedback, and market changes with strategy adjustments to keep your listing competitive.'
            ),
            array(
                'icon' => 'fas fa-gavel',
                'title' => 'Expert Negotiation',
                'content' => 'Aggressive negotiation on your behalf to secure the highest price and best terms, including handling multiple offer situations.'
            )
        ),
        'icon_style' => 'circle',
        'animation' => true,
        'section_id' => 'features-sellers'
    ));
    ?>

    <?php
    // ============================================
    // Stats Section - Selling Success
    // ============================================
    get_template_part('template-parts/sections/stats', null, array(
        'style' => 'counters',
        'theme' => 'primary',
        'padding' => 'xl',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('selling-stats-bg.jpg') : '',
        'overlay' => 'dark',
        'badge' => 'Selling Results',
        'headline' => 'Proven Track Record of Success',
        'subheadline' => 'See why sellers choose The Parker Group to maximize their returns',
        'stats' => array(
            array(
                'number' => '102%',
                'label' => 'Average Sale Price',
                'icon' => 'fas fa-percentage',
                'description' => 'Of original listing price achieved'
            ),
            array(
                'number' => '23',
                'label' => 'Days on Market',
                'icon' => 'fas fa-calendar',
                'description' => 'Average time to sell vs 45 day market average'
            ),
            array(
                'number' => '1,200+',
                'label' => 'Homes Sold',
                'icon' => 'fas fa-home',
                'description' => 'Successfully sold in the last 5 years'
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
    // FAQ Section - Seller Questions
    // ============================================
    get_template_part('template-parts/sections/faq', null, array(
        'background' => 'light',
        'padding' => 'xl',
        'badge' => 'Seller FAQs',
        'headline' => 'Common Home Selling Questions',
        'subheadline' => 'Get expert answers to the questions every home seller asks',
        'layout' => 'accordion',
        'columns' => 1,
        'faqs' => array(
            array(
                'question' => 'How do you determine my home\'s value?',
                'answer' => 'We use a comprehensive approach including recent comparable sales, current market conditions, your home\'s unique features, and local market trends. Our CMA (Comparative Market Analysis) provides accurate pricing based on real data, not online estimates.'
            ),
            array(
                'question' => 'What improvements should I make before selling?',
                'answer' => 'Focus on high-impact, low-cost improvements like fresh paint, decluttering, and curb appeal. We\'ll provide a detailed list of recommended updates based on your budget and expected return on investment. Not all improvements add value, so we help you prioritize wisely.'
            ),
            array(
                'question' => 'How long will it take to sell my home?',
                'answer' => 'Our homes typically sell in 23 days compared to the market average of 45 days. Timeline depends on pricing, condition, and market conditions. We provide realistic expectations and adjust strategy if needed to keep your sale on track.'
            ),
            array(
                'question' => 'What are the costs of selling a home?',
                'answer' => 'Typical costs include agent commissions (usually 5-6%), title insurance, transfer taxes, and any agreed-upon repairs. We provide a detailed net sheet showing all costs upfront so you know exactly what to expect at closing.'
            ),
            array(
                'question' => 'Should I sell now or wait for the market to improve?',
                'answer' => 'Market timing depends on your specific situation and local conditions. We provide current market analysis and forecasts to help you make an informed decision. Sometimes waiting costs more than the potential market gains.'
            ),
            array(
                'question' => 'Can I sell if I still owe money on my mortgage?',
                'answer' => 'Yes, most sellers still have mortgage balances. We\'ll calculate your net proceeds after paying off loans and all selling costs. If you owe more than the home\'s value, we can explore options like short sales or loan modification.'
            )
        ),
        'section_id' => 'faq-sellers'
    ));
    ?>

    <?php
    // ============================================
    // Testimonials Section - Seller Success Stories (COMMENTED OUT)
    // ============================================
    /*
    get_template_part('template-parts/sections/testimonials', null, array(
        'background' => 'white',
        'padding' => 'xl',
        'badge' => 'Success Stories',
        'headline' => 'What Our Sellers Say',
        'subheadline' => 'Real results from clients who trusted us to sell their homes',
        'testimonials_count' => 3,
        'layout' => 'slider',
        'show_ratings' => true,
        'auto_play' => true,
        'section_id' => 'testimonials-sellers'
    ));
    */
    ?>

    <?php
    // ============================================
    // CTA Section - Get Your Home Value (COMMENTED OUT)
    // ============================================
    /*
    get_template_part('template-parts/sections/cta', null, array(
        'layout' => 'split',
        'background' => 'gradient',
        'padding' => '2xl',
        'badge' => 'Ready to Sell?',
        'headline' => 'Get Your Free Home Valuation',
        'subheadline' => 'Discover what your home is worth in today\'s market',
        'content' => 'Receive a detailed market analysis and personalized selling strategy. Our expert team will show you how to maximize your home\'s value and sell quickly.',
        'buttons' => array(
            array(
                'text' => 'Get Free Home Value',
                'url' => '#',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-calculator',
                'data_attributes' => 'data-modal-form="fello-search-widget" data-modal-id="hph-fello-modal" data-modal-title="Get Free Home Valuation" data-modal-subtitle="Discover what your home is worth in today\'s market."'
            ),
            array(
                'text' => 'Schedule Consultation',
                'url' => '#',
                'style' => 'outline-white',
                'size' => 'xl',
                'icon' => 'fas fa-calendar',
                'data_attributes' => 'data-modal-form="general-contact" data-modal-id="hph-consultation-modal" data-modal-title="Schedule Consultation" data-modal-subtitle="Let\'s discuss your real estate goals and how we can help."'
            )
        ),
        'form' => array(
            'title' => 'Quick Home Valuation',
            'button_text' => 'Get My Home Value',
            'modal_trigger' => true,
            'modal_form' => 'fello-search-widget',
            'modal_id' => 'hph-fello-modal',
            'modal_title' => 'Get Your Home Value',
            'modal_subtitle' => 'Find out what your home is worth in today\'s market.',
            'action_url' => '#'
        ),
        'animation' => true,
        'section_id' => 'cta-sellers'
    ));
    */
    ?>
    
    <!-- Market Insights Section (COMMENTED OUT) -->
    <?php
    /*
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'left-image',
        'background' => 'light',
        'padding' => 'xl',
        'alignment' => 'left',
        'badge' => 'Market Insights',
        'headline' => 'Delaware\'s Hot Seller Market',
        'subheadline' => 'Current market conditions favor sellers',
        'content' => 'Delaware\'s real estate market continues to show strong demand from buyers, with limited inventory creating excellent opportunities for sellers. Homes are selling faster and for higher prices than historical averages.<br><br>Our comprehensive market analysis shows that properly priced and marketed homes are receiving multiple offers within the first week on market.',
        'buttons' => array(
            array(
                'text' => 'Get Market Report',
                'url' => '/contact/',
                'style' => 'primary',
                'size' => 'lg',
                'icon' => 'fas fa-chart-line'
            )
        ),
        'image' => array(
            'url' => function_exists('hph_get_image_url') ? hph_get_image_url('market-trends.jpg') : '',
            'alt' => 'Delaware real estate market trends'
        ),
        'animation' => true,
        'section_id' => 'market-insights'
    ));
    */
    ?>
    
    <!-- Selling Advantages Section (COMMENTED OUT) -->
    <?php
    /*
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'grid',
        'background' => 'white',
        'padding' => 'xl',
        'columns' => 2,
        'badge' => 'Selling Advantages',
        'headline' => 'Why Now is the Perfect Time to Sell',
        'subheadline' => 'Current market conditions create unprecedented opportunities',
        'animation' => true,
        'items' => array(
            array(
                'icon' => 'fas fa-trending-up',
                'title' => 'High Buyer Demand',
                'content' => 'More buyers than available homes means competitive offers and faster sales. Multiple offer situations are common.'
            ),
            array(
                'icon' => 'fas fa-dollar-sign',
                'title' => 'Premium Pricing',
                'content' => 'Well-prepared homes are selling at or above asking price. Our average sale-to-list ratio is 102%.'
            ),
            array(
                'icon' => 'fas fa-clock',
                'title' => 'Quick Sales',
                'content' => 'Average days on market is just 23 days compared to the national average of 45 days.'
            ),
            array(
                'icon' => 'fas fa-handshake',
                'title' => 'Smooth Transactions',
                'content' => 'Motivated buyers with solid financing create fewer complications and smoother closings.'
            )
        ),
        'section_id' => 'selling-advantages'
    ));
    */
    ?>

<?php get_footer(); ?>
