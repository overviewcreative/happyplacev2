<?php
/**
 * Template Name: Mortgages Landing Page
 * Simplified page with calculator, Remy's info, and FAQs
 */

get_header(); ?>

<main id="main-content" class="site-main">
    
    <?php
    // ============================================
    // Mortgage Hero Section
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'gradient',
        'theme' => 'primary',
        'height' => 'lg',
        'is_top_of_page' => true,
        'background_image' => function_exists('hph_get_image_url') ? hph_get_image_url('mortgage-hero.jpg') : '',
        'parallax' => true,
        'overlay' => 'gradient',
        'alignment' => 'center',
        'headline' => 'Get Pre-Approved & Find Your Dream Home',
        'subheadline' => 'Expert mortgage guidance from Mustache Mortgages',
        'content' => 'Get pre-approved with confidence and start your home search knowing exactly what you can afford.',
        'content_width' => 'normal',
        'buttons' => array(
            array(
                'text' => 'Calculate Your Payment',
                'url' => '#mortgage-calculator',
                'style' => 'white',
                'size' => 'xl',
                'icon' => 'fas fa-calculator',
                'icon_position' => 'right'
            ),
            array(
                'text' => 'Get Pre-Approved',
                'url' => '#remy-contact',
                'style' => 'outline-white',
                'size' => 'xl'
            )
        ),
        'section_id' => 'mortgage-hero'
    ));
    
    // ============================================
    // Pre-Approval Benefits Section
    // ============================================
    ?>
    
    <section class="hph-section hph-bg-white hph-py-3xl">
        <div class="hph-container">
            <div class="hph-grid hph-grid-cols-2 hph-gap-2xl hph-items-center">
                
                <!-- Content -->
                <div class="hph-preapproval-content">
                    <div class="hph-section-number">01</div>
                    <h2 class="hph-heading-2xl hph-text-primary hph-mb-lg">Pre-Approval or Proof of Funds</h2>
                    <p class="hph-text-lg hph-text-gray-700 hph-mb-xl">
                        Getting pre-approved by a lender is an essential step when you're starting your home search, and provides you with:
                    </p>
                    
                    <div class="hph-benefits-grid hph-grid hph-grid-cols-3 hph-gap-lg hph-mb-xl">
                        <div class="hph-benefit-item">
                            <h3 class="hph-text-lg hph-text-primary hph-font-semibold hph-mb-sm">Market Confidence</h3>
                            <p class="hph-text-sm hph-text-gray-600">Sellers will only consider cash offers with proof of funds or offers from pre-approved buyers.</p>
                        </div>
                        <div class="hph-benefit-item">
                            <h3 class="hph-text-lg hph-text-primary hph-font-semibold hph-mb-sm">Financial Clarity</h3>
                            <p class="hph-text-sm hph-text-gray-600">Know exactly what you can afford before falling in love with properties beyond your budget.</p>
                        </div>
                        <div class="hph-benefit-item">
                            <h3 class="hph-text-lg hph-text-primary hph-font-semibold hph-mb-sm">Smoother Process</h3>
                            <p class="hph-text-sm hph-text-gray-600">Move more easily from offer to closing with your financing already in motion.</p>
                        </div>
                    </div>
                    
                    <div class="hph-expert-text hph-mb-lg">
                        <p class="hph-text-base hph-text-gray-700">
                            Your dedicated agent can connect you with our network of trusted lending partners who offer transparent, straightforward financing solutions tailored to your specific needs.
                        </p>
                    </div>
                    
                    <div class="hph-cash-buyers hph-mb-lg">
                        <p class="hph-text-base hph-text-gray-700">
                            If you're purchasing with cash, we'll help you prepare proper documentation showing proof of funds.
                        </p>
                    </div>
                    
                    <div class="hph-remy-tip hph-card hph-bg-primary-50 hph-border-primary-200 hph-p-lg">
                        <h4 class="hph-text-lg hph-text-primary hph-font-semibold hph-mb-sm">Financing Tips From Remy</h4>
                        <p class="hph-text-base hph-text-gray-700 hph-mb-0">
                            Today's mortgage landscape offers numerous paths to homeownership — from conventional and FHA loans to VA, USDA, and specialized first-time buyer programs. Your agent will ensure you're paired with a lender who understands your homeownership goals.
                        </p>
                    </div>
                </div>
                
                <!-- Image -->
                <div class="hph-preapproval-image">
                    <?php if (function_exists('hph_get_image_url')) : ?>
                    <img src="<?php echo hph_get_image_url('mortgage-preapproval.jpg'); ?>" 
                         alt="Pre-approval documents" 
                         class="hph-img-responsive hph-rounded-lg">
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
    </section>
    
    <?php
    // ============================================
    // Mortgage Calculator Section
    // ============================================
    ?>
    
    <section id="mortgage-calculator" class="hph-section">
        <div class="hph-container">
            
            <div class="hph-section-header">
                <div class="hph-badge hph-badge-primary">Mortgage Calculator</div>
                <h2 class="hph-heading-xl">Calculate Your Monthly Payment</h2>
                <p class="hph-text-lg hph-text-gray-600">Get an estimate of your monthly mortgage payment including principal, interest, taxes, and insurance</p>
            </div>
            
            <div class="hph-grid hph-grid-cols-2 hph-gap-xl hph-items-start">
                
                <!-- Calculator Form -->
                <div class="hph-card hph-p-xl">
                    <h3 class="hph-heading-lg hph-mb-xl">Loan Details</h3>
                    
                    <form id="mortgage-calculator-form" class="hph-form hph-form--stacked">
                        
                        <!-- Home Price -->
                        <div class="hph-form-group">
                            <label for="home-price" class="hph-form-label">Home Price</label>
                            <div class="hph-input-group">
                                <span class="hph-input-prefix">$</span>
                                <input type="number" id="home-price" value="400000" min="0" step="1000" placeholder="400,000">
                            </div>
                        </div>
                        
                        <!-- Down Payment -->
                        <div class="hph-form-group">
                            <label for="down-payment" class="hph-form-label">Down Payment</label>
                            <div class="hph-form-row">
                                <div class="hph-input-group">
                                    <span class="hph-input-prefix">$</span>
                                    <input type="number" id="down-payment" value="80000" min="0" step="1000">
                                </div>
                                <div class="hph-input-group hph-w-20">
                                    <input type="number" id="down-payment-percent" value="20" min="0" max="100" step="0.1">
                                    <span class="hph-input-suffix">%</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Interest Rate -->
                        <div class="hph-form-group">
                            <label for="interest-rate" class="hph-form-label">Interest Rate</label>
                            <div class="hph-input-group">
                                <input type="number" id="interest-rate" value="6.5" min="0" max="20" step="0.01" placeholder="6.5">
                                <span class="hph-input-suffix">%</span>
                            </div>
                        </div>
                        
                        <!-- Loan Term -->
                        <div class="hph-form-group">
                            <label for="loan-term" class="hph-form-label">Loan Term</label>
                            <select id="loan-term">
                                <option value="30">30 years</option>
                                <option value="15">15 years</option>
                                <option value="20">20 years</option>
                                <option value="25">25 years</option>
                            </select>
                        </div>
                        
                        <!-- Property Tax -->
                        <div class="hph-form-group">
                            <label for="property-tax" class="hph-form-label">Property Tax (Annual)</label>
                            <div class="hph-input-group">
                                <span class="hph-input-prefix">$</span>
                                <input type="number" id="property-tax" value="4800" min="0" step="100" placeholder="4,800">
                            </div>
                        </div>
                        
                        <!-- Home Insurance -->
                        <div class="hph-form-group">
                            <label for="home-insurance" class="hph-form-label">Home Insurance (Annual)</label>
                            <div class="hph-input-group">
                                <span class="hph-input-prefix">$</span>
                                <input type="number" id="home-insurance" value="1200" min="0" step="100" placeholder="1,200">
                            </div>
                        </div>
                        
                        <!-- PMI -->
                        <div class="hph-form-group">
                            <label for="pmi" class="hph-form-label">PMI (Monthly)</label>
                            <div class="hph-input-group">
                                <span class="hph-input-prefix">$</span>
                                <input type="number" id="pmi" value="0" min="0" step="10" placeholder="0">
                            </div>
                            <small class="hph-form-help">Usually required if down payment is less than 20%</small>
                        </div>
                        
                    </form>
                </div>
                
                <!-- Results -->
                <div class="hph-calculator-results">
                    
                    <!-- Monthly Payment Card -->
                    <div class="hph-card hph-card-gradient hph-text-center hph-mb-xl">
                        <h3 class="hph-text-lg hph-text-white hph-opacity-90 hph-mb-md">Monthly Payment</h3>
                        <div id="monthly-payment" class="hph-display-1 hph-text-white hph-mb-sm">$2,234</div>
                        <p class="hph-text-white hph-opacity-80 hph-mb-0">Principal, Interest, Taxes & Insurance</p>
                    </div>
                    
                    <!-- Payment Breakdown -->
                    <div class="hph-card hph-mb-lg">
                        <h4 class="hph-heading-lg hph-mb-lg">Payment Breakdown</h4>
                        
                        <div class="hph-breakdown-list">
                            <div class="hph-breakdown-item">
                                <span class="hph-text-gray-600">Principal & Interest</span>
                                <span id="principal-interest" class="hph-font-semibold">$1,834</span>
                            </div>
                            <div class="hph-breakdown-item">
                                <span class="hph-text-gray-600">Property Tax</span>
                                <span id="monthly-tax" class="hph-font-medium">$400</span>
                            </div>
                            <div class="hph-breakdown-item">
                                <span class="hph-text-gray-600">Home Insurance</span>
                                <span id="monthly-insurance" class="hph-font-medium">$100</span>
                            </div>
                            <div class="hph-breakdown-item" id="pmi-row">
                                <span class="hph-text-gray-600">PMI</span>
                                <span id="monthly-pmi" class="hph-font-medium">$0</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Loan Summary -->
                    <div class="hph-card hph-bg-gray-50">
                        <h4 class="hph-heading-lg hph-mb-lg">Loan Summary</h4>
                        
                        <div class="hph-summary-list">
                            <div class="hph-summary-item">
                                <span class="hph-text-gray-600">Loan Amount</span>
                                <span id="loan-amount" class="hph-font-semibold">$320,000</span>
                            </div>
                            <div class="hph-summary-item">
                                <span class="hph-text-gray-600">Total Interest</span>
                                <span id="total-interest" class="hph-font-medium">$340,240</span>
                            </div>
                            <div class="hph-summary-item">
                                <span class="hph-text-gray-600">Total Payments</span>
                                <span id="total-payments" class="hph-font-medium">$660,240</span>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
            </div>
            
            <!-- Disclaimer -->
            <div class="hph-disclaimer hph-card hph-bg-gray-50 hph-text-center hph-mt-2xl">
                <p class="hph-text-sm hph-text-gray-600 hph-mb-0">
                    <strong>Disclaimer:</strong> This calculator provides estimates only. Actual loan terms and payments may vary based on credit history, income, and lender requirements. Contact our mortgage professionals for personalized quotes.
                </p>
            </div>
            
        </div>
    </section>

    <style>
    /* Mortgage Calculator Specific Styles */
    .hph-input-group {
        position: relative;
        display: flex;
        align-items: center;
    }
    
    .hph-input-prefix,
    .hph-input-suffix {
        position: absolute;
        color: var(--hph-gray-600);
        font-weight: 500;
        z-index: 2;
        pointer-events: none;
    }
    
    .hph-input-prefix {
        left: 1rem;
    }
    
    .hph-input-suffix {
        right: 1rem;
    }
    
    .hph-input-group input {
        padding-left: 2.5rem !important;
    }
    
    .hph-input-group input:last-child {
        padding-right: 2.5rem !important;
    }
    
    .hph-form-row {
        display: flex;
        gap: 1rem;
        align-items: end;
    }
    
    .hph-w-20 {
        width: 5rem;
        flex-shrink: 0;
    }
    
    .hph-card-gradient {
        background: linear-gradient(135deg, var(--hph-primary), var(--hph-primary-dark)) !important;
        color: var(--hph-white);
    }
    
    .hph-display-1 {
        font-size: 3rem;
        font-weight: 700;
        line-height: 1;
    }
    
    .hph-breakdown-list,
    .hph-summary-list {
        display: grid;
        gap: 0.75rem;
    }
    
    .hph-breakdown-item,
    .hph-summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--hph-border-color-light);
    }
    
    .hph-breakdown-item:last-child,
    .hph-summary-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
        .hph-grid-cols-2 {
            grid-template-columns: 1fr;
        }
        
        .hph-form-row {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
        
        .hph-w-20 {
            width: 100%;
        }
        
        .hph-display-1 {
            font-size: 2rem;
        }
    }
    </style>

    <?php
    // ============================================
    // Mortgage FAQ Section
    // ============================================
    get_template_part('template-parts/sections/faq', null, array(
        'background' => 'light',
        'padding' => 'xl',
        'badge' => 'Frequently Asked Questions',
        'headline' => 'Mortgage Questions Answered',
        'subheadline' => 'Get answers to the most common mortgage and financing questions',
        'layout' => 'accordion',
        'columns' => 1,
        'faqs' => array(
            array(
                'question' => 'How much can I afford to borrow?',
                'answer' => 'Generally, you can afford a home that costs 2-3 times your annual household income. However, the exact amount depends on your credit score, debt-to-income ratio, down payment, and current interest rates. Our mortgage professionals can help you determine your exact buying power through a pre-approval process.'
            ),
            array(
                'question' => 'What is the minimum down payment required?',
                'answer' => 'Down payment requirements vary by loan type: Conventional loans can require as little as 3% down, FHA loans require 3.5%, VA loans often require 0% down for eligible veterans, and USDA loans may also require 0% down in eligible rural areas. Higher down payments typically result in better rates and lower monthly payments.'
            ),
            array(
                'question' => 'What is PMI and when is it required?',
                'answer' => 'Private Mortgage Insurance (PMI) is typically required when your down payment is less than 20% of the home\'s value. PMI protects the lender if you default on the loan. The cost is usually 0.3% to 1.5% of the original loan amount annually. Once you have 20% equity in your home, you can request PMI removal.'
            ),
            array(
                'question' => 'How long does the mortgage approval process take?',
                'answer' => 'Pre-approval can often be completed within 24-48 hours. Full mortgage approval and closing typically takes 30-45 days from the time you submit a complete application. Factors that can affect timing include property appraisal, title search, and document processing. Working with experienced professionals can help streamline the process.'
            ),
            array(
                'question' => 'What documents do I need for a mortgage application?',
                'answer' => 'You\'ll typically need: Recent pay stubs, W-2s or tax returns (2 years), Bank statements (2-3 months), Employment verification letter, Credit report authorization, Asset documentation for investments/retirement accounts, and Documentation of any additional income sources. Self-employed borrowers may need additional documentation.'
            ),
            array(
                'question' => 'Should I choose a fixed or adjustable rate mortgage?',
                'answer' => 'Fixed-rate mortgages offer stable payments throughout the loan term, making budgeting easier. Adjustable-rate mortgages (ARMs) typically start with lower rates but can fluctuate over time. Choose fixed-rate if you plan to stay long-term and want payment predictability, or ARM if you plan to move or refinance within a few years.'
            ),
            array(
                'question' => 'What are closing costs and who pays them?',
                'answer' => 'Closing costs typically range from 2-5% of the loan amount and include appraisal fees, title insurance, attorney fees, recording fees, and lender fees. Buyers usually pay most closing costs, but some can be negotiated with the seller. Some loan programs allow rolling closing costs into the mortgage or offer reduced-cost options.'
            ),
            array(
                'question' => 'Can I get a mortgage with less-than-perfect credit?',
                'answer' => 'Yes, there are options for borrowers with various credit profiles. FHA loans accept credit scores as low as 580 (500 with 10% down), VA loans are flexible with credit requirements, and some conventional loans accept scores around 620. Lower scores may result in higher interest rates, so improving your credit before applying can save money.'
            )
        ),
        'section_id' => 'mortgage-faq'
    ));
    
    // ============================================
    // Mustache Mortgage CTA Section
    // ============================================
    ?>
    
    <section id="get-preapproved" class="hph-section hph-mortgage-cta-section" style="padding: var(--hph-spacing-3xl) 0; background: linear-gradient(135deg, var(--hph-color-primary), var(--hph-color-secondary)); color: var(--hph-color-white);">
        <div class="hph-container">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--hph-spacing-3xl); align-items: center;" class="hph-mortgage-cta-grid">
                
                <!-- CTA Content -->
                <div class="hph-cta-content">
                    <div style="margin-bottom: var(--hph-spacing-lg);">
                        <span style="display: inline-block; padding: var(--hph-spacing-sm) var(--hph-spacing-md); background: rgba(255, 255, 255, 0.2); border-radius: var(--hph-radius-full); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold); margin-bottom: var(--hph-spacing-lg);">Preferred Partner</span>
                    </div>
                    
                    <h2 style="margin: 0 0 var(--hph-spacing-md) 0; font-size: var(--hph-text-3xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                        Ready to Get Pre-Approved?
                    </h2>
                    
                    <p style="margin: 0 0 var(--hph-spacing-xl) 0; font-size: var(--hph-text-lg); opacity: 0.9; line-height: var(--hph-leading-relaxed);">
                        Connect with <strong>Remy Haynes</strong> at <strong>Mustache Mortgage</strong> - our trusted mortgage partner with over 15 years of experience helping clients secure the best financing options.
                    </p>
                    
                    <!-- Partner Benefits -->
                    <div style="margin-bottom: var(--hph-spacing-xl);">
                        <ul style="margin: 0; padding: 0; list-style: none; display: grid; gap: var(--hph-spacing-sm);">
                            <li style="display: flex; align-items: center; gap: var(--hph-spacing-sm);">
                                <i class="fas fa-check-circle" style="color: rgba(255, 255, 255, 0.8);"></i>
                                <span>Competitive rates and flexible terms</span>
                            </li>
                            <li style="display: flex; align-items: center; gap: var(--hph-spacing-sm);">
                                <i class="fas fa-check-circle" style="color: rgba(255, 255, 255, 0.8);"></i>
                                <span>Fast pre-approval process (often same day)</span>
                            </li>
                            <li style="display: flex; align-items: center; gap: var(--hph-spacing-sm);">
                                <i class="fas fa-check-circle" style="color: rgba(255, 255, 255, 0.8);"></i>
                                <span>Multiple loan programs including FHA, VA, Conventional</span>
                            </li>
                            <li style="display: flex; align-items: center; gap: var(--hph-spacing-sm);">
                                <i class="fas fa-check-circle" style="color: rgba(255, 255, 255, 0.8);"></i>
                                <span>Dedicated support throughout the entire process</span>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Contact Info -->
                    <div style="margin-bottom: var(--hph-spacing-xl); padding: var(--hph-spacing-lg); background: rgba(255, 255, 255, 0.1); border-radius: var(--hph-radius-lg);">
                        <h4 style="margin: 0 0 var(--hph-spacing-sm) 0; font-size: var(--hph-text-lg); font-weight: var(--hph-font-semibold);">Remy Haynes</h4>
                        <p style="margin: 0 0 var(--hph-spacing-md) 0; opacity: 0.9;">Senior Mortgage Advisor, Mustache Mortgage</p>
                        
                        <div style="display: flex; gap: var(--hph-spacing-lg); margin-bottom: var(--hph-spacing-md);">
                            <a href="tel:+1234567890" style="display: flex; align-items: center; gap: var(--hph-spacing-xs); color: var(--hph-color-white); text-decoration: none; opacity: 0.9; transition: opacity 0.2s;">
                                <i class="fas fa-phone"></i>
                                <span>(123) 456-7890</span>
                            </a>
                            <a href="mailto:remy@mustache-mortgage.com" style="display: flex; align-items: center; gap: var(--hph-spacing-xs); color: var(--hph-color-white); text-decoration: none; opacity: 0.9; transition: opacity 0.2s;">
                                <i class="fas fa-envelope"></i>
                                <span>remy@mustache-mortgage.com</span>
                            </a>
                        </div>
                        
                        <p style="margin: 0; font-size: var(--hph-text-sm); opacity: 0.8;">
                            NMLS #123456 | Licensed in multiple states
                        </p>
                    </div>
                    
                </div>
                
                <!-- Contact Form -->
                <div class="hph-mortgage-form">
                    <div style="background: var(--hph-color-white); padding: var(--hph-spacing-2xl); border-radius: var(--hph-radius-xl); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);">
                        
                        <h3 style="margin: 0 0 var(--hph-spacing-md) 0; color: var(--hph-color-dark); font-size: var(--hph-text-xl); font-weight: var(--hph-font-semibold); text-align: center;">
                            Get Your Pre-Approval Started
                        </h3>
                        
                        <p style="margin: 0 0 var(--hph-spacing-xl) 0; color: var(--hph-color-gray-600); text-align: center;">
                            Fill out this form and Remy will contact you within 24 hours
                        </p>
                        
                        <form id="mortgage-preapproval-form" style="display: grid; gap: var(--hph-spacing-lg);">
                            
                            <!-- Name Fields -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--hph-spacing-md);">
                                <div class="hph-form-group">
                                    <label for="first-name" style="display: block; margin-bottom: var(--hph-spacing-xs); color: var(--hph-color-dark); font-weight: var(--hph-font-medium);">First Name *</label>
                                    <input type="text" id="first-name" name="first_name" required style="width: 100%; padding: var(--hph-spacing-md); border: 1px solid var(--hph-color-border); border-radius: var(--hph-radius-md); font-size: var(--hph-text-base);">
                                </div>
                                <div class="hph-form-group">
                                    <label for="last-name" style="display: block; margin-bottom: var(--hph-spacing-xs); color: var(--hph-color-dark); font-weight: var(--hph-font-medium);">Last Name *</label>
                                    <input type="text" id="last-name" name="last_name" required style="width: 100%; padding: var(--hph-spacing-md); border: 1px solid var(--hph-color-border); border-radius: var(--hph-radius-md); font-size: var(--hph-text-base);">
                                </div>
                            </div>
                            
                            <!-- Contact Fields -->
                            <div class="hph-form-group">
                                <label for="email" style="display: block; margin-bottom: var(--hph-spacing-xs); color: var(--hph-color-dark); font-weight: var(--hph-font-medium);">Email Address *</label>
                                <input type="email" id="email" name="email" required style="width: 100%; padding: var(--hph-spacing-md); border: 1px solid var(--hph-color-border); border-radius: var(--hph-radius-md); font-size: var(--hph-text-base);">
                            </div>
                            
                            <div class="hph-form-group">
                                <label for="phone" style="display: block; margin-bottom: var(--hph-spacing-xs); color: var(--hph-color-dark); font-weight: var(--hph-font-medium);">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" required style="width: 100%; padding: var(--hph-spacing-md); border: 1px solid var(--hph-color-border); border-radius: var(--hph-radius-md); font-size: var(--hph-text-base);">
                            </div>
                            
                            <!-- Loan Details -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--hph-spacing-md);">
                                <div class="hph-form-group">
                                    <label for="purchase-price" style="display: block; margin-bottom: var(--hph-spacing-xs); color: var(--hph-color-dark); font-weight: var(--hph-font-medium);">Target Purchase Price</label>
                                    <select id="purchase-price" name="purchase_price" style="width: 100%; padding: var(--hph-spacing-md); border: 1px solid var(--hph-color-border); border-radius: var(--hph-radius-md); font-size: var(--hph-text-base);">
                                        <option value="">Select Range</option>
                                        <option value="under-300k">Under $300,000</option>
                                        <option value="300k-500k">$300,000 - $500,000</option>
                                        <option value="500k-750k">$500,000 - $750,000</option>
                                        <option value="750k-1m">$750,000 - $1,000,000</option>
                                        <option value="over-1m">Over $1,000,000</option>
                                    </select>
                                </div>
                                <div class="hph-form-group">
                                    <label for="down-payment-amount" style="display: block; margin-bottom: var(--hph-spacing-xs); color: var(--hph-color-dark); font-weight: var(--hph-font-medium);">Down Payment</label>
                                    <select id="down-payment-amount" name="down_payment" style="width: 100%; padding: var(--hph-spacing-md); border: 1px solid var(--hph-color-border); border-radius: var(--hph-radius-md); font-size: var(--hph-text-base);">
                                        <option value="">Select Amount</option>
                                        <option value="3-percent">3% - 5%</option>
                                        <option value="5-10-percent">5% - 10%</option>
                                        <option value="10-20-percent">10% - 20%</option>
                                        <option value="20-plus-percent">20%+</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Timeline -->
                            <div class="hph-form-group">
                                <label for="timeline" style="display: block; margin-bottom: var(--hph-spacing-xs); color: var(--hph-color-dark); font-weight: var(--hph-font-medium);">When are you looking to buy?</label>
                                <select id="timeline" name="timeline" style="width: 100%; padding: var(--hph-spacing-md); border: 1px solid var(--hph-color-border); border-radius: var(--hph-radius-md); font-size: var(--hph-text-base);">
                                    <option value="">Select Timeline</option>
                                    <option value="immediately">Immediately</option>
                                    <option value="1-3-months">1-3 months</option>
                                    <option value="3-6-months">3-6 months</option>
                                    <option value="6-12-months">6-12 months</option>
                                    <option value="just-exploring">Just exploring options</option>
                                </select>
                            </div>
                            
                            <!-- Message -->
                            <div class="hph-form-group">
                                <label for="message" style="display: block; margin-bottom: var(--hph-spacing-xs); color: var(--hph-color-dark); font-weight: var(--hph-font-medium);">Additional Information (Optional)</label>
                                <textarea id="message" name="message" rows="3" style="width: 100%; padding: var(--hph-spacing-md); border: 1px solid var(--hph-color-border); border-radius: var(--hph-radius-md); font-size: var(--hph-text-base); resize: vertical;" placeholder="Tell us about your specific needs or questions..."></textarea>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="submit" style="width: 100%; padding: var(--hph-spacing-lg); background: linear-gradient(135deg, var(--hph-color-primary), var(--hph-color-secondary)); color: var(--hph-color-white); border: none; border-radius: var(--hph-radius-md); font-size: var(--hph-text-lg); font-weight: var(--hph-font-semibold); cursor: pointer; transition: all 0.3s ease;">
                                <i class="fas fa-paper-plane" style="margin-right: var(--hph-spacing-sm);"></i>
                                Get Pre-Approval Started
                            </button>
                            
                            <!-- Privacy Notice -->
                            <p style="margin: var(--hph-spacing-md) 0 0 0; font-size: var(--hph-text-xs); color: var(--hph-color-gray-500); text-align: center;">
                                By submitting this form, you consent to be contacted by Mustache Mortgage regarding your mortgage needs. We respect your privacy and will not share your information with third parties.
                            </p>
                            
                        </form>
                        
                    </div>
                </div>
                
            </div>
            
        </div>
    </section>

    <?php
    // ============================================
    // Final CTA Section
    // ============================================
    get_template_part('template-parts/sections/cta', null, array(
        'layout' => 'centered',
        'background' => 'light',
        'padding' => 'xl',
        'headline' => 'Questions About Mortgages?',
        'subheadline' => 'Our real estate and mortgage professionals are here to help',
        'content' => 'Contact our team for personalized guidance on financing your dream home.',
        'buttons' => array(
            array(
                'text' => 'Contact Our Team',
                'url' => '/contact/',
                'style' => 'primary',
                'size' => 'xl',
                'icon' => 'fas fa-phone'
            ),
            array(
                'text' => 'Browse Properties',
                'url' => '/listing/',
                'style' => 'outline',
                'size' => 'xl',
                'icon' => 'fas fa-home'
            )
        ),
        'animation' => true,
        'section_id' => 'mortgage-final-cta'
    ));
    ?>
    
</main>

<!-- Mortgage Calculator JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Mortgage Calculator Logic
    const calculator = {
        homePrice: document.getElementById('home-price'),
        downPayment: document.getElementById('down-payment'),
        downPaymentPercent: document.getElementById('down-payment-percent'),
        interestRate: document.getElementById('interest-rate'),
        loanTerm: document.getElementById('loan-term'),
        propertyTax: document.getElementById('property-tax'),
        homeInsurance: document.getElementById('home-insurance'),
        pmi: document.getElementById('pmi'),
        
        // Result elements
        monthlyPayment: document.getElementById('monthly-payment'),
        principalInterest: document.getElementById('principal-interest'),
        monthlyTax: document.getElementById('monthly-tax'),
        monthlyInsurance: document.getElementById('monthly-insurance'),
        monthlyPmi: document.getElementById('monthly-pmi'),
        loanAmount: document.getElementById('loan-amount'),
        totalInterest: document.getElementById('total-interest'),
        totalPayments: document.getElementById('total-payments'),
        
        init: function() {
            // Add event listeners to all inputs
            [this.homePrice, this.downPayment, this.downPaymentPercent, this.interestRate, 
             this.loanTerm, this.propertyTax, this.homeInsurance, this.pmi].forEach(input => {
                input.addEventListener('input', () => this.calculate());
                input.addEventListener('change', () => this.calculate());
            });
            
            // Sync down payment dollar amount and percentage
            this.downPayment.addEventListener('input', () => this.updateDownPaymentPercent());
            this.downPaymentPercent.addEventListener('input', () => this.updateDownPaymentAmount());
            
            // Initial calculation
            this.calculate();
        },
        
        updateDownPaymentPercent: function() {
            const homePrice = parseFloat(this.homePrice.value) || 0;
            const downPayment = parseFloat(this.downPayment.value) || 0;
            if (homePrice > 0) {
                const percentage = (downPayment / homePrice * 100).toFixed(1);
                this.downPaymentPercent.value = percentage;
            }
        },
        
        updateDownPaymentAmount: function() {
            const homePrice = parseFloat(this.homePrice.value) || 0;
            const percentage = parseFloat(this.downPaymentPercent.value) || 0;
            const downPayment = (homePrice * percentage / 100).toFixed(0);
            this.downPayment.value = downPayment;
        },
        
        calculate: function() {
            // Get input values
            const homePrice = parseFloat(this.homePrice.value) || 0;
            const downPayment = parseFloat(this.downPayment.value) || 0;
            const annualRate = parseFloat(this.interestRate.value) || 0;
            const loanTermYears = parseInt(this.loanTerm.value) || 30;
            const annualPropertyTax = parseFloat(this.propertyTax.value) || 0;
            const annualInsurance = parseFloat(this.homeInsurance.value) || 0;
            const monthlyPmiAmount = parseFloat(this.pmi.value) || 0;
            
            // Calculate loan amount
            const loanAmount = homePrice - downPayment;
            
            // Calculate monthly payment (Principal & Interest)
            const monthlyRate = annualRate / 100 / 12;
            const numberOfPayments = loanTermYears * 12;
            
            let monthlyPI = 0;
            if (monthlyRate > 0 && numberOfPayments > 0 && loanAmount > 0) {
                monthlyPI = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / 
                           (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
            }
            
            // Calculate other monthly costs
            const monthlyPropertyTax = annualPropertyTax / 12;
            const monthlyInsurance = annualInsurance / 12;
            
            // Total monthly payment
            const totalMonthlyPayment = monthlyPI + monthlyPropertyTax + monthlyInsurance + monthlyPmiAmount;
            
            // Calculate totals
            const totalInterestPaid = (monthlyPI * numberOfPayments) - loanAmount;
            const totalOfAllPayments = monthlyPI * numberOfPayments;
            
            // Update display
            this.monthlyPayment.textContent = '$' + this.formatNumber(totalMonthlyPayment);
            this.principalInterest.textContent = '$' + this.formatNumber(monthlyPI);
            this.monthlyTax.textContent = '$' + this.formatNumber(monthlyPropertyTax);
            this.monthlyInsurance.textContent = '$' + this.formatNumber(monthlyInsurance);
            this.monthlyPmi.textContent = '$' + this.formatNumber(monthlyPmiAmount);
            
            this.loanAmount.textContent = '$' + this.formatNumber(loanAmount);
            this.totalInterest.textContent = '$' + this.formatNumber(totalInterestPaid);
            this.totalPayments.textContent = '$' + this.formatNumber(totalOfAllPayments);
            
            // Show/hide PMI row
            const pmiRow = document.getElementById('pmi-row');
            if (monthlyPmiAmount > 0) {
                pmiRow.style.display = 'flex';
            } else {
                pmiRow.style.display = 'none';
            }
            
            // Auto-calculate PMI if down payment is less than 20%
            const downPaymentPercent = downPayment / homePrice * 100;
            if (downPaymentPercent < 20 && monthlyPmiAmount === 0) {
                // Estimate PMI at 0.5% of loan amount annually
                const estimatedPMI = (loanAmount * 0.005) / 12;
                this.pmi.value = Math.round(estimatedPMI);
                this.calculate(); // Recalculate with PMI
            }
        },
        
        formatNumber: function(num) {
            if (isNaN(num) || num < 0) return '0';
            return Math.round(num).toLocaleString();
        }
    };
    
    // Initialize calculator
    calculator.init();
    
    // Pre-approval form handling
    const preapprovalForm = document.getElementById('mortgage-preapproval-form');
    if (preapprovalForm) {
        preapprovalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            const requiredFields = ['first-name', 'last-name', 'email', 'phone'];
            let isValid = true;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--hph-color-error)';
                } else {
                    field.style.borderColor = 'var(--hph-color-border)';
                }
            });
            
            if (!isValid) {
                alert('Please fill in all required fields.');
                return;
            }
            
            // Here you would normally send the data to your backend
            // For now, show a success message
            alert('Thank you! Remy will contact you within 24 hours to discuss your pre-approval.');
            
            // Reset form
            preapprovalForm.reset();
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
});

// Responsive handling for calculator
window.addEventListener('resize', function() {
    const calculatorGrid = document.querySelector('.hph-calculator-grid');
    const ctaGrid = document.querySelector('.hph-mortgage-cta-grid');
    
    if (window.innerWidth <= 768) {
        if (calculatorGrid) {
            calculatorGrid.style.gridTemplateColumns = '1fr';
            calculatorGrid.style.gap = 'var(--hph-spacing-xl)';
        }
        if (ctaGrid) {
            ctaGrid.style.gridTemplateColumns = '1fr';
            ctaGrid.style.gap = 'var(--hph-spacing-xl)';
        }
    } else {
        if (calculatorGrid) {
            calculatorGrid.style.gridTemplateColumns = '1fr 1fr';
            calculatorGrid.style.gap = 'var(--hph-spacing-2xl)';
        }
        if (ctaGrid) {
            ctaGrid.style.gridTemplateColumns = '1fr 1fr';
            ctaGrid.style.gap = 'var(--hph-spacing-3xl)';
        }
    }
});
</script>

<style>
/* ====================================
   MORTGAGE CALCULATOR - HPH FRAMEWORK
   ==================================== */

/* Section Layout */
.hph-mortgage-calculator-section {
    padding: var(--hph-section-padding-y) 0;
    background: var(--hph-gradient-primary-subtle);
    position: relative;
}

.hph-mortgage-calculator-section .hph-container {
    max-width: var(--hph-container-xl);
    margin: 0 auto;
    padding: 0 var(--hph-space-6);
}

/* Section Header */
.hph-mortgage-calculator-section .hph-section-header {
    text-align: center;
    margin-bottom: var(--hph-space-4xl);
}

.hph-mortgage-calculator-section .hph-badge {
    display: inline-block;
    padding: var(--hph-space-2) var(--hph-space-6);
    background: var(--hph-gradient-primary);
    color: var(--hph-white);
    border-radius: var(--hph-radius-full);
    font-size: var(--hph-text-sm);
    font-weight: var(--hph-font-semibold);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: var(--hph-space-6);
    box-shadow: var(--hph-shadow-primary);
}

.hph-mortgage-calculator-section .hph-section-title {
    font-size: var(--hph-text-4xl);
    font-weight: var(--hph-font-bold);
    color: var(--hph-text-color);
    margin: 0 0 var(--hph-space-4) 0;
    line-height: var(--hph-leading-tight);
    font-family: var(--hph-font-display);
}

.hph-mortgage-calculator-section .hph-section-subtitle {
    font-size: var(--hph-text-lg);
    color: var(--hph-text-muted);
    max-width: 60ch;
    margin: 0 auto;
    line-height: var(--hph-leading-relaxed);
}

/* Calculator Grid */
.hph-calculator-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--hph-gap-2xl);
    align-items: start;
}

/* Calculator Form Styling */
.hph-calculator-form {
    background: var(--hph-white);
    padding: var(--hph-card-padding-lg);
    border-radius: var(--hph-radius-2xl);
    box-shadow: var(--hph-shadow-xl);
    border: var(--hph-border-width) solid var(--hph-border-color-light);
    position: relative;
}

.hph-calculator-form::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--hph-gradient-primary);
    border-radius: var(--hph-radius-2xl) var(--hph-radius-2xl) 0 0;
}

.hph-calculator-form h3 {
    margin: 0 0 var(--hph-space-8) 0;
    font-size: var(--hph-text-xl);
    font-weight: var(--hph-font-semibold);
    color: var(--hph-text-color);
    display: flex;
    align-items: center;
    gap: var(--hph-space-2);
}

.hph-calculator-form h3::before {
    content: '🏠';
    font-size: var(--hph-text-lg);
}

#mortgage-calculator-form {
    display: grid;
    gap: var(--hph-space-6);
}

/* Form Groups and Labels */
.hph-form-group label {
    display: block;
    margin-bottom: var(--hph-space-1);
    font-weight: var(--hph-font-medium);
    color: var(--hph-text-color);
    font-size: var(--hph-text-sm);
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

/* Input Styling with HPH Variables */
.hph-mortgage-calculator-section input[type="number"],
.hph-mortgage-calculator-section input[type="text"],
.hph-mortgage-calculator-section select {
    width: 100%;
    padding: var(--hph-form-input-padding-y) var(--hph-form-input-padding-x);
    border: var(--hph-input-border);
    border-radius: var(--hph-input-radius);
    font-size: var(--hph-text-base);
    font-weight: var(--hph-font-medium);
    color: var(--hph-text-color);
    background: var(--hph-white);
    transition: var(--hph-transition);
    font-family: var(--hph-font-primary);
}

.hph-mortgage-calculator-section input[type="number"]:focus,
.hph-mortgage-calculator-section input[type="text"]:focus,
.hph-mortgage-calculator-section select:focus {
    outline: none;
    border-color: var(--hph-primary);
    box-shadow: var(--hph-input-focus-ring);
    transform: translateY(-1px);
}

/* Input with Currency Symbol */
.hph-form-group [style*="position: relative"] span {
    position: absolute;
    left: var(--hph-space-4);
    top: 50%;
    transform: translateY(-50%);
    color: var(--hph-text-muted);
    font-weight: var(--hph-font-semibold);
    font-size: var(--hph-text-base);
    z-index: 2;
}

.hph-form-group input[style*="padding-left"] {
    padding-left: var(--hph-space-2xl);
}

/* Grid Layout for Split Fields */
.hph-form-group [style*="grid-template-columns"] {
    display: grid;
    gap: var(--hph-gap-md);
}

/* Results Display */
.hph-calculator-results {
    display: flex;
    flex-direction: column;
    gap: var(--hph-gap-lg);
}

/* Monthly Payment Card - Using HPH Classes */
.hph-payment-summary {
    background: var(--hph-gradient-primary);
    color: var(--hph-white);
    padding: var(--hph-card-padding-lg);
    border-radius: var(--hph-radius-2xl);
    text-align: center;
    position: relative;
    overflow: hidden;
    box-shadow: var(--hph-shadow-primary);
}

.hph-payment-summary h3 {
    margin: 0 0 var(--hph-space-4) 0;
    font-size: var(--hph-text-lg);
    font-weight: var(--hph-font-medium);
    opacity: 0.9;
    position: relative;
    z-index: 2;
}

.hph-payment-summary #monthly-payment {
    font-size: var(--hph-text-5xl);
    font-weight: var(--hph-font-extrabold);
    line-height: var(--hph-leading-none);
    margin-bottom: var(--hph-space-2);
    position: relative;
    z-index: 2;
    font-family: var(--hph-font-display);
}

.hph-payment-summary p {
    margin: 0;
    opacity: 0.8;
    font-size: var(--hph-text-base);
    position: relative;
    z-index: 2;
}

/* Payment Breakdown Card */
.hph-payment-breakdown {
    background: var(--hph-white);
    border: var(--hph-border-width) solid var(--hph-border-color);
    border-radius: var(--hph-card-radius);
    padding: var(--hph-card-padding);
    box-shadow: var(--hph-card-shadow);
}

.hph-payment-breakdown h4 {
    margin: 0 0 var(--hph-space-6) 0;
    font-size: var(--hph-text-xl);
    font-weight: var(--hph-font-semibold);
    color: var(--hph-text-color);
    display: flex;
    align-items: center;
    gap: var(--hph-space-2);
}

.hph-payment-breakdown h4::before {
    content: '📊';
    font-size: var(--hph-text-lg);
}

.hph-payment-breakdown > div {
    display: grid;
    gap: var(--hph-gap-md);
}

.hph-payment-breakdown > div > div {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--hph-space-4) 0;
    border-bottom: var(--hph-border-width) solid var(--hph-border-color-light);
    transition: var(--hph-transition-fast);
}

.hph-payment-breakdown > div > div:hover {
    background-color: var(--hph-gray-25);
    margin: 0 calc(-1 * var(--hph-space-4));
    padding-left: var(--hph-space-4);
    padding-right: var(--hph-space-4);
    border-radius: var(--hph-radius-md);
}

.hph-payment-breakdown > div > div:last-child {
    border-bottom: none;
    font-weight: var(--hph-font-semibold);
    color: var(--hph-text-color);
}

.hph-payment-breakdown span:first-child {
    color: var(--hph-text-muted);
    font-size: var(--hph-text-sm);
}

.hph-payment-breakdown span:last-child {
    font-weight: var(--hph-font-semibold);
    color: var(--hph-text-color);
    font-size: var(--hph-text-base);
}

/* Loan Summary */
.hph-loan-summary {
    background: var(--hph-gray-25);
    border-radius: var(--hph-radius-lg);
    padding: var(--hph-space-6);
    border: var(--hph-border-width) solid var(--hph-border-color);
}

.hph-loan-summary h4 {
    margin: 0 0 var(--hph-space-4) 0;
    font-size: var(--hph-text-lg);
    font-weight: var(--hph-font-semibold);
    color: var(--hph-text-color);
    display: flex;
    align-items: center;
    gap: var(--hph-space-2);
}

.hph-loan-summary h4::before {
    content: '💰';
    font-size: var(--hph-text-base);
}

.hph-loan-summary > div {
    display: grid;
    gap: var(--hph-gap-sm);
}

.hph-loan-summary > div > div {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--hph-space-1) 0;
}

.hph-loan-summary span:first-child {
    color: var(--hph-text-muted);
    font-size: var(--hph-text-sm);
}

.hph-loan-summary span:last-child {
    font-weight: var(--hph-font-semibold);
    color: var(--hph-text-color);
}

/* Disclaimer */
.hph-mortgage-calculator-section [style*="margin-top: var(--hph-spacing-2xl)"] {
    margin-top: var(--hph-space-2xl);
    padding: var(--hph-space-6);
    background: var(--hph-primary-25);
    border-radius: var(--hph-radius-lg);
    text-align: center;
    border: var(--hph-border-width) solid var(--hph-primary-100);
}

.hph-mortgage-calculator-section [style*="margin-top: var(--hph-spacing-2xl)"] p {
    margin: 0;
    font-size: var(--hph-text-sm);
    color: var(--hph-text-muted);
    line-height: var(--hph-leading-relaxed);
}

.hph-mortgage-calculator-section [style*="margin-top: var(--hph-spacing-2xl)"] strong {
    color: var(--hph-primary);
    font-weight: var(--hph-font-semibold);
}

/* CTA Section - Using HPH Framework */
.hph-mortgage-cta-section {
    padding: var(--hph-section-padding-y) 0;
    background: var(--hph-gradient-primary);
    color: var(--hph-white);
    position: relative;
}

.hph-mortgage-cta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--hph-gap-3xl);
    align-items: center;
}

.hph-cta-content h2 {
    font-size: var(--hph-text-3xl);
    font-weight: var(--hph-font-bold);
    margin: 0 0 var(--hph-space-6) 0;
    line-height: var(--hph-leading-tight);
    font-family: var(--hph-font-display);
}

.hph-cta-content > p {
    font-size: var(--hph-text-lg);
    opacity: 0.9;
    line-height: var(--hph-leading-relaxed);
    margin: 0 0 var(--hph-space-8) 0;
}

.hph-cta-content ul {
    margin: 0 0 var(--hph-space-8) 0;
    padding: 0;
    list-style: none;
    display: grid;
    gap: var(--hph-gap-sm);
}

.hph-cta-content li {
    display: flex;
    align-items: center;
    gap: var(--hph-space-2);
    font-size: var(--hph-text-base);
}

.hph-cta-content li i {
    color: rgba(255, 255, 255, 0.8);
    font-size: var(--hph-text-lg);
}

/* Contact Info Card */
.hph-cta-content > div[style*="padding"] {
    padding: var(--hph-space-6);
    background: rgba(255, 255, 255, 0.1);
    border-radius: var(--hph-radius-lg);
    backdrop-filter: blur(10px);
    border: var(--hph-border-width) solid rgba(255, 255, 255, 0.2);
    margin-bottom: var(--hph-space-8);
}

.hph-cta-content h4 {
    margin: 0 0 var(--hph-space-1) 0;
    font-size: var(--hph-text-xl);
    font-weight: var(--hph-font-semibold);
}

/* Mortgage Form */
.hph-mortgage-form > div {
    background: var(--hph-white);
    padding: var(--hph-card-padding-lg);
    border-radius: var(--hph-radius-xl);
    box-shadow: var(--hph-shadow-2xl);
    border: var(--hph-border-width) solid rgba(255, 255, 255, 0.2);
}

.hph-mortgage-form h3 {
    margin: 0 0 var(--hph-space-2) 0;
    color: var(--hph-text-color);
    font-size: var(--hph-text-xl);
    font-weight: var(--hph-font-semibold);
    text-align: center;
    font-family: var(--hph-font-display);
}

.hph-mortgage-form > div > p {
    margin: 0 0 var(--hph-space-8) 0;
    color: var(--hph-text-muted);
    text-align: center;
    font-size: var(--hph-text-base);
}

/* Mortgage Form Inputs */
.hph-mortgage-form input,
.hph-mortgage-form select,
.hph-mortgage-form textarea {
    width: 100%;
    padding: var(--hph-form-input-padding-y) var(--hph-form-input-padding-x);
    border: var(--hph-input-border);
    border-radius: var(--hph-input-radius);
    font-size: var(--hph-text-base);
    color: var(--hph-text-color);
    background: var(--hph-white);
    transition: var(--hph-transition);
    font-family: var(--hph-font-primary);
}

.hph-mortgage-form input:focus,
.hph-mortgage-form select:focus,
.hph-mortgage-form textarea:focus {
    outline: none;
    border-color: var(--hph-primary);
    box-shadow: var(--hph-input-focus-ring);
}

.hph-mortgage-form label {
    display: block;
    margin-bottom: var(--hph-space-1);
    color: var(--hph-text-color);
    font-weight: var(--hph-font-medium);
    font-size: var(--hph-text-sm);
}

.hph-mortgage-form .hph-form-group {
    margin-bottom: var(--hph-space-6);
}

.hph-mortgage-form [style*="grid-template-columns"] {
    display: grid;
    gap: var(--hph-gap-md);
}

.hph-mortgage-form button {
    width: 100%;
    padding: var(--hph-btn-padding-lg-y) var(--hph-btn-padding-lg-x);
    background: var(--hph-gradient-primary);
    color: var(--hph-white);
    border: none;
    border-radius: var(--hph-btn-radius);
    font-size: var(--hph-text-lg);
    font-weight: var(--hph-font-semibold);
    cursor: pointer;
    transition: var(--hph-transition);
    box-shadow: var(--hph-shadow-primary);
    font-family: var(--hph-font-primary);
}

.hph-mortgage-form button:hover {
    transform: translateY(-2px);
    box-shadow: var(--hph-shadow-xl);
}

.hph-mortgage-form button i {
    margin-right: var(--hph-space-2);
}

/* Privacy Notice */
.hph-mortgage-form p:last-child {
    margin: var(--hph-space-6) 0 0 0;
    font-size: var(--hph-text-xs);
    color: var(--hph-text-muted);
    text-align: center;
    line-height: var(--hph-leading-relaxed);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hph-calculator-grid,
    .hph-mortgage-cta-grid {
        grid-template-columns: 1fr;
        gap: var(--hph-gap-xl);
    }
    
    .hph-mortgage-form {
        order: -1;
    }
    
    .hph-form-group [style*="grid-template-columns"] {
        grid-template-columns: 1fr;
    }
    
    .hph-mortgage-form [style*="grid-template-columns"] {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .hph-mortgage-calculator-section {
        padding: var(--hph-padding-3xl) 0;
    }
    
    .hph-calculator-form,
    .hph-mortgage-form > div {
        padding: var(--hph-space-6);
    }
    
    .hph-payment-summary #monthly-payment {
        font-size: var(--hph-text-4xl);
    }
    
    .hph-mortgage-calculator-section input[type="number"],
    .hph-mortgage-calculator-section input[type="text"],
    .hph-mortgage-calculator-section select,
    .hph-mortgage-form input,
    .hph-mortgage-form select,
    .hph-mortgage-form textarea {
        font-size: 16px; /* Prevents zoom on iOS */
    }
}
</style>

<?php get_footer(); ?>
