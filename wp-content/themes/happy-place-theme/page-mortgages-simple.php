<?php
/**
 * Template Name: Mortgages Landing Page - Simplified
 * Rebuilt using proper framework components and template parts
 * 
 * @package HappyPlaceTheme
 * @version 3.0.0
 */

get_header(); ?>

<main id="main-content" class="hph-main">
    
    <?php
    // Hero Section
    get_template_part('template-parts/sections/hero', null, [
        'style' => 'gradient',
        'theme' => 'primary',
        'height' => 'lg',
        'is_top_of_page' => true,
        'alignment' => 'center',
        'headline' => 'Get Pre-Approved for Your Dream Home',
        'subheadline' => 'Expert mortgage guidance to help you move forward with confidence',
        'content' => 'Know exactly what you can afford before you start shopping. Get pre-approved quickly and easily with our trusted mortgage partner.',
        'buttons' => [
            [
                'text' => 'Get Pre-Approved Now',
                'url' => 'https://myloan.mustachemortgages.com/homehub/signup/rhaynes@mustachemortgages.com',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-arrow-right',
                'target' => '_blank'
            ],
            [
                'text' => 'Learn More',
                'url' => '#why-preapproval',
                'style' => 'outline-white',
                'size' => 'xl'
            ]
        ]
    ]);
    ?>
    
    <?php
    // Why Pre-Approval Matters - Features Section
    get_template_part('template-parts/sections/features', null, [
        'background' => 'white',
        'padding' => 'xl',
        'columns' => 3,
        'alignment' => 'center',
        'headline' => 'Why Pre-Approval Matters',
        'subheadline' => 'In today\'s competitive market, sellers want to know you\'re a serious buyer. Pre-approval gives you the edge you need.',
        'section_id' => 'why-preapproval',
        'features' => [
            [
                'icon' => 'fas fa-check-circle',
                'title' => 'Know Your Budget',
                'content' => 'Shop with confidence knowing exactly what you can afford before you fall in love with a home.'
            ],
            [
                'icon' => 'fas fa-handshake',
                'title' => 'Stronger Offers',
                'content' => 'Sellers prefer pre-approved buyers. Your offer will stand out in multiple offer situations.'
            ],
            [
                'icon' => 'fas fa-clock',
                'title' => 'Faster Closing',
                'content' => 'With financing already in motion, you can move from offer to closing more quickly.'
            ]
        ]
    ]);
    ?>
    
    <!-- Getting Started & Remy Contact Section -->
    <section class="hph-section" style="padding: var(--hph-spacing-3xl) 0; background: var(--hph-white);">
        <div class="hph-container">
            <div class="hph-grid hph-grid-cols-1 hph-grid-cols-lg-2 hph-gap-xl hph-items-center">
                
                <!-- CTA Content Card -->
                <div class="hph-card hph-card-elevated hph-bg-white hph-rounded-lg hph-shadow-lg hph-p-xl">
                    <h3 class="hph-text-2xl hph-font-semibold hph-mb-md">Getting Started is Easy</h3>
                    <p class="hph-text-gray-700 hph-mb-md">
                        Our recommended mortgage partner, Mustache Mortgages, offers a streamlined online application process. 
                        The process typically takes 24-48 hours, and you'll receive a pre-approval letter that shows sellers you mean business.
                    </p>
                    <div class="hph-mb-md">
                        <a href="https://myloan.mustachemortgages.com/homehub/signup/rhaynes@mustachemortgages.com" 
                           target="_blank" 
                           class="hph-btn hph-btn-primary hph-btn-lg hph-mb-md">
                            Start Your Pre-Approval Application
                            <i class="fas fa-external-link-alt hph-ml-2"></i>
                        </a>
                    </div>
                    <p class="hph-text-sm hph-text-gray-500">
                        <em>Note: You are not required to use Mustache Mortgages. You're free to shop around and choose any lender that best meets your needs.</em>
                    </p>
                </div>
                
                <!-- Remy Contact Card -->
                <div class="hph-card hph-card-elevated hph-bg-white hph-rounded-lg hph-shadow-lg hph-p-xl">
                    <div class="hph-text-center">
                        <div class="hph-rounded-full hph-bg-primary hph-text-white hph-mx-auto hph-mb-md hph-flex hph-items-center hph-justify-center" style="width: 96px; height: 96px;">
                            <span class="hph-text-2xl hph-font-bold">RH</span>
                        </div>
                        
                        <h4 class="hph-text-xl hph-font-bold hph-mb-xs">Remy Haynes</h4>
                        <p class="hph-text-sm hph-text-gray-600 hph-mb-xs">Loan Officer</p>
                        <p class="hph-text-xs hph-text-gray-500 hph-mb-md">NMLS #2433656</p>
                        
                        <div class="hph-text-left hph-border-t hph-pt-md">
                            <p class="hph-text-sm hph-mb-sm">
                                <i class="fas fa-phone hph-text-primary hph-mr-sm"></i>
                                <a href="tel:302-504-3396" class="hph-text-gray-700 hover:hph-text-primary">302-504-3396</a>
                            </p>
                            <p class="hph-text-sm">
                                <i class="fas fa-envelope hph-text-primary hph-mr-sm"></i>
                                <a href="mailto:rhaynes@mustachemortgages.com" class="hph-text-gray-700 hover:hph-text-primary hph-text-xs">rhaynes@mustachemortgages.com</a>
                            </p>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>
    
    <!-- Meet Remy Section -->
    <section class="hph-section" style="padding: var(--hph-spacing-3xl) 0; background: var(--hph-gray-50);">
        <div class="hph-container">
            <div class="hph-grid hph-grid-cols-1 hph-grid-cols-lg-2 hph-gap-2xl hph-items-center">
                
                <div class="hph-text-center hph-text-lg-left">
                    <h2 class="hph-text-3xl hph-font-bold hph-mb-md">Meet Your Mortgage Expert</h2>
                    <h3 class="hph-text-2xl hph-text-primary hph-mb-sm">Remy Haynes</h3>
                    <p class="hph-text-gray-600 hph-mb-lg">Loan Officer | NMLS #2433656</p>
                    
                    <p class="hph-text-lg hph-mb-lg">
                        With years of experience in the Delaware mortgage market, Remy specializes in helping first-time buyers, 
                        move-up buyers, and investors secure the right financing for their needs.
                    </p>
                    
                    <p class="hph-mb-xl">
                        Remy takes pride in making the mortgage process simple and stress-free. Whether you're looking for 
                        conventional, FHA, VA, or USDA financing, Remy will guide you through your options and help you 
                        choose the loan that's right for you.
                    </p>
                    
                    <a href="https://myloan.mustachemortgages.com/homehub/signup/rhaynes@mustachemortgages.com" 
                       target="_blank" 
                       class="hph-btn hph-btn-primary hph-btn-lg hph-mb-md">
                        Get Pre-Approved with Remy
                        <i class="fas fa-external-link-alt hph-ml-2"></i>
                    </a>
                    
                    <p class="hph-text-sm hph-text-gray-500">
                        <em>Clients are not required to use Mustache Mortgages. There are other mortgage lenders available 
                        and you are free to shop around for the best services and rates.</em>
                    </p>
                </div>
                
                <!-- Enhanced Remy Contact Card -->
                <div class="hph-card hph-card-elevated hph-bg-white hph-rounded-lg hph-shadow-xl hph-p-xl">
                    <div class="hph-text-center">
                        <div class="hph-rounded-full hph-bg-primary hph-text-white hph-mx-auto hph-mb-lg hph-flex hph-items-center hph-justify-center hph-w-36 hph-h-36">
                            <span class="hph-text-4xl hph-font-bold">RH</span>
                        </div>
                        
                        <h4 class="hph-text-2xl hph-font-bold hph-mb-sm">Remy Haynes</h4>
                        <p class="hph-text-gray-600 hph-mb-xs">Loan Officer</p>
                        <p class="hph-text-sm hph-text-gray-500 hph-mb-lg">NMLS #2433656</p>
                        
                        <div class="hph-border-t hph-pt-lg">
                            <div class="hph-text-left hph-space-y-md">
                                <a href="tel:302-504-3396" class="hph-flex hph-items-center hph-text-gray-700 hover:hph-text-primary">
                                    <i class="fas fa-phone hph-text-primary hph-mr-md hph-w-5"></i>
                                    <span class="hph-font-medium">302-504-3396</span>
                                </a>
                                <a href="mailto:rhaynes@mustachemortgages.com" class="hph-flex hph-items-center hph-text-gray-700 hover:hph-text-primary">
                                    <i class="fas fa-envelope hph-text-primary hph-mr-md hph-w-5"></i>
                                    <span class="hph-text-sm">rhaynes@mustachemortgages.com</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>
    
    <?php
    // FAQ Section using framework component
    $faqs = [
        [
            'question' => 'How much do I need for a down payment?',
            'answer' => 'Down payment requirements vary by loan type. FHA loans require as little as 3.5% down, conventional loans can go as low as 3%, and VA/USDA loans may offer 0% down options for qualified buyers. A 20% down payment on conventional loans avoids the need for mortgage insurance.'
        ],
        [
            'question' => 'What credit score do I need?',
            'answer' => 'Credit score requirements depend on the loan type. Conventional loans typically require a minimum score of 620, FHA loans may accept scores as low as 580 (or 500 with 10% down), and VA loans are often more flexible. Remember, higher credit scores generally qualify for better interest rates.'
        ],
        [
            'question' => 'How long does the pre-approval process take?',
            'answer' => 'With all required documents ready, pre-approval can often be completed within 24-48 hours. The full mortgage process from application to closing typically takes 30-45 days, though this can vary based on the complexity of your situation.'
        ],
        [
            'question' => 'What documents will I need to provide?',
            'answer' => 'Common documents include: recent pay stubs (last 30 days), W-2s (last 2 years), tax returns (last 2 years), bank statements (last 2 months), driver\'s license, and employment verification. Self-employed borrowers may need additional documentation such as profit/loss statements.'
        ],
        [
            'question' => 'What\'s the difference between pre-qualification and pre-approval?',
            'answer' => 'Pre-qualification is an informal estimate based on self-reported information. Pre-approval involves a formal application, credit check, and documentation review, resulting in a conditional commitment from the lender. Pre-approval carries much more weight with sellers.'
        ],
        [
            'question' => 'Can I get pre-approved if I\'m self-employed?',
            'answer' => 'Yes! Self-employed borrowers can absolutely get pre-approved. You\'ll typically need to provide additional documentation such as two years of tax returns, profit/loss statements, and possibly bank statements to verify your income.'
        ],
        [
            'question' => 'Should I get pre-approved before looking at homes?',
            'answer' => 'Yes, getting pre-approved before house hunting is highly recommended. It helps you understand your budget, makes your offers more competitive, and can speed up the closing process once you find your perfect home.'
        ],
        [
            'question' => 'Do I have to use the lender who pre-approved me?',
            'answer' => 'No, you\'re not obligated to use the lender who provided your pre-approval. You\'re free to shop around for the best rates and terms. However, if you switch lenders, you\'ll need to go through the approval process again with the new lender.'
        ]
    ];
    
    get_template_part('template-parts/sections/faq', null, [
        'style' => 'accordion',
        'theme' => 'white',
        'padding' => 'xl',
        'alignment' => 'center',
        'headline' => 'Frequently Asked Questions',
        'subheadline' => 'Get answers to common mortgage questions',
        'faqs' => $faqs,
        'accordion_style' => 'clean'
    ]);
    ?>
    
    <?php
    // Final CTA Section
    get_template_part('template-parts/sections/cta', null, [
        'layout' => 'split',
        'background' => 'primary',
        'padding' => 'xl',
        'alignment' => 'left',
        'headline' => 'Ready to Take the Next Step?',
        'content' => 'Don\'t let financing uncertainty hold you back from finding your dream home. Get pre-approved today and start shopping with confidence.',
        'buttons' => [
            [
                'text' => 'Get Pre-Approved Now',
                'url' => 'https://myloan.mustachemortgages.com/homehub/signup/rhaynes@mustachemortgages.com',
                'style' => 'white',
                'size' => 'lg',
                'icon' => 'fas fa-external-link-alt',
                'target' => '_blank'
            ]
        ],
        'form' => '
            <div class="hph-bg-white hph-text-gray-900 hph-rounded-lg hph-p-lg hph-shadow-xl">
                <div class="hph-flex hph-items-center hph-gap-lg">
                    <div class="hph-rounded-full hph-bg-primary hph-text-white hph-flex-shrink-0 hph-flex hph-items-center hph-justify-center hph-w-20 hph-h-20">
                        <span class="hph-text-2xl hph-font-bold">RH</span>
                    </div>
                    
                    <div class="hph-flex-grow">
                        <h5 class="hph-text-lg hph-font-bold hph-mb-xs">Remy Haynes</h5>
                        <p class="hph-text-sm hph-text-gray-600 hph-mb-xs">Loan Officer | NMLS #2433656</p>
                        <a href="tel:302-504-3396" class="hph-text-primary hph-font-semibold hover:hph-text-primary-dark">
                            <i class="fas fa-phone hph-mr-xs"></i> 302-504-3396
                        </a>
                        <p class="hph-text-sm hph-opacity-75 hph-mt-sm">
                            Remember: You\'re not required to use Mustache Mortgages and are free to choose any lender.
                        </p>
                    </div>
                </div>
            </div>
        '
    ]);
    ?>

</main>

<?php get_footer(); ?>