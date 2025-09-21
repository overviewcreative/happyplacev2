<?php
/**
 * Template Name: About Page
 * Description: The Parker Group about page with team, values, and story sections
 */

get_header(); ?>

<main id="main-content" class="site-main">
    
    <?php
    // ============================================
    // About Hero Section
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'image',
        'theme' => 'primary',
        'height' => 'lg',
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('corn-field-delaware.jpg') : '',
        'parallax' => true,
        'overlay' => 'gradient',
        'alignment' => 'left',
        'headline' => 'Live Local, Give Local, Love Local',
        'subheadline' => 'Your Local Real Estate Advisors Since 2016',
        'content' => 'We\'re more than real estate agents – we\'re your neighbors, teachers turned advisors, and passionate advocates for Sussex County communities.',
        'content_width' => 'normal',
        'fade_in' => true,
        'scroll_indicator' => false,
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
                'text' => 'Let\'s Connect',
                'url' => '#',
                'data_attributes' => 'data-modal-form="general-contact" data-modal-title="Start Your Journey" data-modal-subtitle="Whether buying or selling, we\'re here to guide you home."',
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
        'layout' => 'left-image',
        'background' => 'light',
        'padding' => 'xl',
        'alignment' => 'left',
        'image_style' => 'circle',
        'image_size' => 'medium',
        'image' => array(
            'url' => function_exists('hph_get_image_url') ? hph_get_image_url('dustin-rachel.jpg') : '',
            'alt' => 'Dustin & Rachel Parker of The Parker Group',
            'image_style' => 'circle',
            'fade_in' => true,
        ),
        'headline' => 'From Classroom to Community',
        'subheadline' => 'A New Approach to Real Estate',
        'content' => '<p>When Dustin and Rachel Parker traded their teaching careers at Sussex Academy for real estate in 2015, they brought something special with them – a genuine passion for education, service, and helping others succeed. What started as Dustin taking real estate classes on the side has grown into one of Sussex County\'s most trusted real estate teams.</p><br>
        <p>As a ninth-generation Sussex County native, Dustin\'s roots run deep in Delaware. Rachel brings her Eastern Shore of Maryland heritage to the team, allowing us to serve families across the Delmarva region. Our backgrounds in politics and education didn\'t just shape our careers – they shaped our commitment to clarity, empowerment, and making a real difference in people\'s lives.</p>
        <p>Today, with nearly 60 agents and a dedicated support team across four locations in Georgetown, Lewes, Milford, and Bridgeville, we\'re reimagining what real estate can be – locally focused, tech-enabled, and always centered on you.</p>',
    
        'section_id' => 'company-story'
    ));
    
    // ============================================
    // Mission Statement Section
    // ============================================
    get_template_part('template-parts/sections/content', null, array(
        'style' => 'centered',
        'background' => 'dark',
        'theme' => 'dark',
        'padding' => 'lg',
        'container' => 'narrow',
        'headline' => 'Our Mission',
        'content' => '<p class="lead text-center"><strong>"We serve our community by helping others find their happy place."</strong></p>
        <p class="text-center">It\'s more than finding a house – it\'s about discovering where you belong. We believe in creating a future where finding your happy place is faster, easier, and more personalized than ever before. Through innovative marketing, cutting-edge technology, and unwavering dedication to exceptional service, we\'re transforming the real estate landscape while building stronger, more connected communities.</p>',
        'section_id' => 'mission'
    ));
    
    // ============================================
    // Core Values Section
    // ============================================
    get_template_part('template-parts/sections/features', null, array(
        'style' => 'cards',
        'theme' => 'light',
        'columns' => 4,
        'icon_style' => 'circle',
        'padding' => 'xl',
        'headline' => 'What Drives Us',
        'subheadline' => 'The values that guide every interaction',
        'features' => array(
            array(
                'icon' => 'fas fa-home',
                'title' => 'Community First',
                'description' => 'We\'re invested in Sussex County. Through our #GiveLocal initiative, every home sold helps support local causes like the SPCA, Boys and Girls Club, Home of the Brave, and Habitat for Humanity.'
            ),
            array(
                'icon' => 'fas fa-graduation-cap',
                'title' => 'Education & Empowerment',
                'description' => 'As former educators, we believe knowledge is power. We guide you through every step, ensuring you understand the process and feel confident in your decisions.'
            ),
            array(
                'icon' => 'fas fa-lightbulb',
                'title' => 'Innovation with Purpose',
                'description' => 'We embrace technology and creative marketing not just to be different, but to serve you better. Our tech-enabled approach makes your real estate journey smoother and more efficient.'
            ),
            array(
                'icon' => 'fas fa-handshake',
                'title' => 'Relationships Over Transactions',
                'description' => 'We\'re your advisors, not just agents. We forge lasting relationships built on trust, transparency, and genuine care for your long-term success.'
            ),
            array(
                'icon' => 'fas fa-users',
                'title' => 'Collaborative Excellence',
                'description' => 'Our team approach means you get the collective expertise of our entire group. We work together, value diverse perspectives, and celebrate each other\'s successes.'
            ),
            array(
                'icon' => 'fas fa-heart',
                'title' => 'Kindness Matters',
                'description' => 'Real estate can be stressful. We approach every situation with respect, empathy, and compassion, making your journey as smooth as possible.'
            ),
            array(
                'icon' => 'fas fa-chart-line',
                'title' => 'Continuous Growth',
                'description' => 'We never stop learning. Through ongoing education and training, we stay ahead of market trends and best practices to serve you better.'
            ),
            array(
                'icon' => 'fas fa-shield-alt',
                'title' => 'Accountability',
                'description' => 'We own our commitments and continuously improve. Your trust drives us to exceed expectations in everything we do.'
            )
        ),
        'section_id' => 'values'
    ));
    
    // ============================================
    // Community Impact Section
    // ============================================
get_template_part('template-parts/sections/content', null, array(
        'layout' => 'left-image',
        'background' => 'light',
        'padding' => 'xl',
        'alignment' => 'left',
        'image_style' => 'circle',
        'image_size' => 'medium',
        'image' => array(
            'url' => function_exists('hph_get_image_url') ? hph_get_image_url('community-giving.jpg') : '',
            'alt' => 'Parker Group team volunteering',
            'image_style' => 'circle',
            'fade_in' => true,
        ),
        'headline' => 'Giving Back to the Community We Love',
        'subheadline' => '#GiveLocal & #GetLocal',
        'content' => '<p>As a locally-grown company, we believe in reinvesting in the community that has given us so much. With every home sold, we give back through our #GiveLocal initiative, supporting organizations that make Sussex County a better place to live.</p>
        <p>From the Sussex County SPCA to the Boys and Girls Club, from Home of the Brave to Habitat for Humanity, we\'re proud to support the causes that strengthen our community. Our team members are required to contribute their time to community service because we believe real success is measured by the positive impact we make.</p>
        <p>Through our #GetLocal initiative, we also spotlight and support other local businesses, encouraging everyone to shop local and keep our community thriving. When you work with us, you\'re not just getting exceptional real estate service – you\'re contributing to the betterment of Sussex County.</p>',
        'section_id' => 'community-impact'
    ));
    
    // ============================================
    // Recognition & Awards Section
    // ============================================
    get_template_part('template-parts/sections/features', null, array(
        'style' => 'list',
        'theme' => 'white',
        'columns' => 2,
        'padding' => 'lg',
        'headline' => 'Recognition & Awards',
        'subheadline' => 'Honored to be recognized for our service and innovation',
        'features' => array(
            array(
                'icon' => 'fas fa-trophy',
                'title' => 'Delaware Today\'s Top Real Estate Agents 2025',
                'description' => 'Recognized among Delaware\'s elite real estate professionals'
            ),
            array(
                'icon' => 'fas fa-award',
                'title' => 'Delaware Good Neighbor Award',
                'description' => 'Honored by the Delaware Association of Realtors for extraordinary community service'
            ),
            array(
                'icon' => 'fas fa-star',
                'title' => 'Best of Sussex County',
                'description' => 'Multiple wins from Metropolitan Magazine and Coastal Style Magazine'
            ),
            array(
                'icon' => 'fas fa-medal',
                'title' => 'Delaware 40 Under 40',
                'description' => 'Recognized for business leadership and community impact'
            ),
            array(
                'icon' => 'fas fa-video',
                'title' => 'BombBomb Video Influencer Award',
                'description' => 'Leading the way in innovative real estate marketing'
            ),
            array(
                'icon' => 'fas fa-chart-line',
                'title' => 'Rookie of the Year Awards',
                'description' => 'Our agents recognized for two consecutive years by Sussex County Association of Realtors'
            )
        ),
        'section_id' => 'awards'
    ));

    // ============================================
    // Our Approach Section
    // ============================================
    get_template_part('template-parts/sections/content', null, array(
        'style' => 'centered',
        'theme' => 'light',
        'padding' => 'xl',
        'container' => 'default',
        'headline' => 'A Different Kind of Real Estate Experience',
        'subheadline' => 'Where innovation meets personal touch',
        'content' => '<p>We\'re reimagining real estate by combining the best of both worlds: cutting-edge technology and genuine human connection. Our innovative team model means you\'re never just working with one person – you have access to our collective expertise, resources, and support network.</p>
        <p>We\'re pioneering new approaches to make real estate more accessible, including salaried positions with full benefits for our team members. This allows us to attract and retain the best talent – professionals who are passionate about serving you, not just chasing commissions. The result? A more stable, knowledgeable team focused on your long-term success.</p>
        <p>From our award-winning marketing strategies to our commitment to continuous education, everything we do is designed to make your real estate journey smoother, faster, and more successful. We\'re not just keeping up with the market – we\'re helping to shape its future.</p>',
        'section_id' => 'our-approach'
    ));
    
    // ============================================
    // Call to Action
    // ============================================
    get_template_part('template-parts/sections/cta', null, array(
        'style' => 'centered',
        'theme' => 'gradient',
        'padding' => 'xl',
        'headline' => 'Ready to Find Your Happy Place?',
        'subheadline' => 'Let\'s start your journey home together',
        'content' => '<p>Whether you\'re buying your first home, selling a beloved family property, or investing in your future, we\'re here to guide you every step of the way. Experience the difference of working with a team that truly cares about your success and your community.</p>',
        'buttons' => array(
            array(
                'text' => 'Drop Us a Line',
                'url' => '/contact',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-phone',
                'icon_position' => 'right'
            ),
            array(
                'text' => 'Find Your Happy Place',
                'url' => '/listings',
                'style' => 'outline-white',
                'size' => 'xl'
            )
        ),
        'section_id' => 'about-cta'
    ));
    ?>
    
</main>

<?php get_footer(); ?>
