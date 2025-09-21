<?php
/**
 * Template Name: Buyers Page
 * 
 * Comprehensive page for home buyers with relevant sections and information
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

get_header();
?>

    <?php
    // ============================================
    // Hero Section - For Home Buyers
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'gradient',
        'theme' => 'primary',
        'height' => 'lg',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('buyers-hero.jpg') : '',
        'parallax' => true,
        'overlay' => 'gradient',
        'alignment' => 'center',
        'headline' => 'Find Your Dream Home',
        'subheadline' => 'Expert guidance and comprehensive support throughout your home buying journey',
        'content' => 'Whether you\'re a first-time buyer or looking to upgrade, we\'ll help you navigate the market and find the perfect property that fits your lifestyle and budget.',
        'content_width' => 'normal',
        'buttons' => array(
            array(
                'text' => 'Browse Properties',
                'url' => '/listing/',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-search',
                'icon_position' => 'right'
            ),
            array(
                'text' => 'Get Pre-Approved',
                'url' => '/contact/',
                'style' => 'outline-white',
                'size' => 'xl',
                'icon' => 'fas fa-calculator'
            )
        ),
        'section_id' => 'hero-buyers'
    ));
    ?>

    <?php
    // ============================================
    // Process Section - Home Buying Process
    // ============================================
    get_template_part('template-parts/sections/process', null, array(
        'background' => 'light',
        'padding' => 'xl',
        'badge' => 'The Buying Process',
        'headline' => 'Your Path to Homeownership',
        'subheadline' => 'Follow our proven step-by-step process to make your home buying experience smooth and successful',
        'layout' => 'timeline',
        'steps' => array(
            array(
                'number' => '01',
                'title' => 'Get Pre-Approved',
                'content' => 'Start with mortgage pre-approval to understand your budget and show sellers you\'re a serious buyer. We\'ll connect you with trusted lenders.',
                'icon' => 'fas fa-calculator'
            ),
            array(
                'number' => '02',
                'title' => 'Define Your Needs',
                'content' => 'Work with our agents to identify your must-haves, nice-to-haves, and deal-breakers. We\'ll help you prioritize features within your budget.',
                'icon' => 'fas fa-list-check'
            ),
            array(
                'number' => '03',
                'title' => 'Search & Tour Properties',
                'content' => 'Access our extensive database and schedule tours of homes that match your criteria. We\'ll provide market insights for each property.',
                'icon' => 'fas fa-home'
            ),
            array(
                'number' => '04',
                'title' => 'Make an Offer',
                'content' => 'When you find "the one," we\'ll help you craft a competitive offer and handle all negotiations to get you the best possible deal.',
                'icon' => 'fas fa-handshake'
            ),
            array(
                'number' => '05',
                'title' => 'Inspection & Appraisal',
                'content' => 'Coordinate professional inspections and appraisals to ensure the property is in good condition and priced fairly.',
                'icon' => 'fas fa-search-plus'
            ),
            array(
                'number' => '06',
                'title' => 'Finalize & Close',
                'content' => 'Complete final paperwork, coordinate with lenders, and attend closing to receive your keys. Congratulations on your new home!',
                'icon' => 'fas fa-key'
            )
        ),
        'section_id' => 'process-buyers'
    ));
    ?>

    <?php
    // ============================================
    // Features Section - Why Buy With Us
    // ============================================
    get_template_part('template-parts/sections/features', null, array(
        'layout' => 'grid',
        'background' => 'white',
        'padding' => 'xl',
        'columns' => 3,
        'badge' => 'Why Choose Happy Place',
        'headline' => 'Your Trusted Home Buying Partner',
        'subheadline' => 'Experience the difference of working with dedicated buyer specialists',
        'content' => 'Our buyer-focused approach ensures you have all the tools, knowledge, and support needed to make confident decisions.',
        'features' => array(
            array(
                'icon' => 'fas fa-search',
                'title' => 'Extensive Property Search',
                'content' => 'Access MLS listings, off-market properties, and new construction opportunities. Get notified instantly when properties matching your criteria become available.'
            ),
            array(
                'icon' => 'fas fa-chart-line',
                'title' => 'Market Analysis & Insights',
                'content' => 'Receive detailed market reports, price comparisons, and neighborhood analytics to make informed decisions about your investment.'
            ),
            array(
                'icon' => 'fas fa-shield-alt',
                'title' => 'Buyer Representation',
                'content' => 'We work exclusively for you, not the seller. Our buyer agents negotiate aggressively to protect your interests and get you the best deal.'
            ),
            array(
                'icon' => 'fas fa-home',
                'title' => 'Virtual & In-Person Tours',
                'content' => 'Take virtual tours from anywhere and schedule private showings. We\'ll provide detailed insights about each property during tours.'
            ),
            array(
                'icon' => 'fas fa-users',
                'title' => 'Professional Network',
                'content' => 'Access our network of trusted lenders, inspectors, attorneys, and contractors to support every aspect of your home purchase.'
            ),
            array(
                'icon' => 'fas fa-clock',
                'title' => '24/7 Support',
                'content' => 'Get immediate responses to your questions and concerns. We\'re available when you need us, including evenings and weekends.'
            )
        ),
        'icon_style' => 'circle',
        'animation' => true,
        'section_id' => 'features-buyers'
    ));
    ?>

    <?php
    // ============================================
    // Featured Properties Section
    // ============================================
    get_template_part('template-parts/sections/featured-properties', null, array(
        'background' => 'light',
        'padding' => 'xl',
        'badge' => 'Properties for Buyers',
        'headline' => 'Homes Perfect for You',
        'subheadline' => 'Explore properties that match what buyers are looking for right now',
        'properties_count' => 6,
        'show_filters' => true,
        'layout' => 'grid',
        'section_id' => 'featured-buyers'
    ));
    ?>

    <?php
    // ============================================
    // FAQ Section - Buyer Questions
    // ============================================
    get_template_part('template-parts/sections/faq', null, array(
        'background' => 'white',
        'padding' => 'xl',
        'badge' => 'Buyer FAQs',
        'headline' => 'Common Home Buying Questions',
        'subheadline' => 'Get answers to the questions every home buyer asks',
        'layout' => 'accordion',
        'columns' => 1,
        'faqs' => array(
            array(
                'question' => 'How much house can I afford?',
                'answer' => 'Generally, you can afford a home that costs 2.5-3 times your annual income, but this depends on your debts, down payment, and other factors. We recommend getting pre-approved to know your exact budget and working with our agents to find properties that fit comfortably within your means.'
            ),
            array(
                'question' => 'How much should I put down on a home?',
                'answer' => 'While 20% down is traditional, many buyers put down less. FHA loans require as little as 3.5% down, and some conventional loans accept 3% down. We\'ll help you understand your options and the pros and cons of different down payment amounts.'
            ),
            array(
                'question' => 'What are closing costs and how much should I expect?',
                'answer' => 'Closing costs typically range from 2-5% of the home\'s purchase price and include lender fees, title insurance, inspections, and other services. We\'ll provide a detailed estimate early in the process so you can budget accordingly.'
            ),
            array(
                'question' => 'Should I buy a home that needs work?',
                'answer' => 'It depends on your budget, timeline, and renovation experience. Fixer-uppers can offer great value but require additional investment and time. We\'ll help you evaluate the true cost of improvements and whether the investment makes sense.'
            ),
            array(
                'question' => 'How competitive is the current market?',
                'answer' => 'Market conditions vary by location and price range. We provide real-time market analysis and help you develop competitive strategies, whether that means moving quickly, offering above asking price, or finding properties with less competition.'
            ),
            array(
                'question' => 'What happens if the home inspection reveals problems?',
                'answer' => 'You typically have several options: negotiate with the seller for repairs or credits, accept the issues as-is, or walk away from the deal. We\'ll help you understand the severity of any issues and negotiate the best outcome for your situation.'
            )
        ),
        'section_id' => 'faq-buyers'
    ));
    ?>

    <?php
    // ============================================
    // Testimonials Section - Buyer Success Stories
    // ============================================
    get_template_part('template-parts/sections/testimonials', null, array(
        'background' => 'primary',
        'padding' => 'xl',
        'badge' => 'Happy Buyers',
        'headline' => 'Success Stories from Our Buyers',
        'subheadline' => 'Hear from clients who found their dream homes with our help',
        'testimonials_count' => 3,
        'layout' => 'slider',
        'show_ratings' => true,
        'auto_play' => true,
        'section_id' => 'testimonials-buyers'
    ));
    ?>

    <?php
    // ============================================
    // CTA Section - Start Your Search
    // ============================================
    get_template_part('template-parts/sections/cta', null, array(
        'layout' => 'split',
        'background' => 'gradient',
        'padding' => '2xl',
        'badge' => 'Ready to Buy?',
        'headline' => 'Let\'s Find Your Perfect Home',
        'subheadline' => 'Start your home buying journey with expert guidance every step of the way',
        'content' => 'Get pre-approved, explore properties, and receive personalized support from our dedicated buyer specialists. Your dream home is waiting.',
        'buttons' => array(
            array(
                'text' => 'Start Property Search',
                'url' => '/listing/',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-search'
            ),
            array(
                'text' => 'Contact a Buyer Agent',
                'url' => '/contact/',
                'style' => 'outline-white',
                'size' => 'xl',
                'icon' => 'fas fa-user'
            )
        ),
        'form' => array(
            'title' => 'Get Started Today',
            'button_text' => 'Connect with an Agent'
        ),
        'animation' => true,
        'section_id' => 'cta-buyers'
    ));
    ?>
    
    <!-- First-Time Buyer Benefits Section -->
    <?php
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'right-image',
        'background' => 'light',
        'padding' => 'xl',
        'alignment' => 'left',
        'badge' => 'First-Time Buyers',
        'headline' => 'Special Programs for New Homeowners',
        'subheadline' => 'Delaware offers exceptional opportunities for first-time buyers',
        'content' => 'Take advantage of Delaware\'s first-time homebuyer programs, including down payment assistance, reduced interest rates, and tax credits. We\'ll help you navigate available grants and programs to minimize your upfront costs.<br><br>Many of our first-time buyers are surprised to learn they can purchase a home with as little as 3% down, and some programs offer even lower requirements.',
        'buttons' => array(
            array(
                'text' => 'Explore Programs',
                'url' => '#mortgage-calculator',
                'style' => 'primary',
                'size' => 'lg',
                'icon' => 'fas fa-gift'
            )
        ),
        'image' => array(
            'url' => function_exists('hph_get_image_url') ? hph_get_image_url('first-time-buyers.jpg') : '',
            'alt' => 'First-time homebuyer programs'
        ),
        'animation' => true,
        'section_id' => 'first-time-benefits'
    ));
    ?>
    
    <!-- Buyer Resources Section -->
    <?php
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'grid',
        'background' => 'white',
        'padding' => 'xl',
        'columns' => 3,
        'badge' => 'Buyer Resources',
        'headline' => 'Everything You Need to Know',
        'subheadline' => 'Comprehensive guides and tools for informed decisions',
        'animation' => true,
        'items' => array(
            array(
                'icon' => 'fas fa-map-marked-alt',
                'title' => 'Neighborhood Guides',
                'content' => 'Detailed information about Delaware communities, schools, amenities, and lifestyle factors to help you choose the perfect location.',
                'link' => array(
                    'text' => 'Explore Areas',
                    'url' => '/communities/'
                )
            ),
            array(
                'icon' => 'fas fa-calculator',
                'title' => 'Mortgage Calculator',
                'content' => 'Calculate monthly payments, compare loan options, and understand the true cost of homeownership with our advanced tools.',
                'link' => array(
                    'text' => 'Calculate Now',
                    'url' => '/mortgages/'
                )
            ),
            array(
                'icon' => 'fas fa-file-contract',
                'title' => 'Buying Guide',
                'content' => 'Step-by-step walkthrough of the home buying process, from pre-approval to closing day. Download our complete buyer\'s guide.',
                'link' => array(
                    'text' => 'Download Guide',
                    'url' => '#'
                )
            )
        ),
        'section_id' => 'buyer-resources'
    ));
    ?>

<?php get_footer(); ?>
