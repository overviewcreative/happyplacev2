<?php
/**
 * Template Name: Mortgages Landing Page - Simplified
 * Clean, focused page with calculator, Remy's info, and essential FAQs
 */

get_header(); ?>

<main id="main-content" class="site-main">
    
    <?php
    // ============================================
    // Hero Section
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'gradient',
        'theme' => 'primary',
        'height' => 'lg',
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
    ?>
    
    <!-- Pre-Approval Benefits Section -->
    <section class="hph-section hph-bg-white hph-py-3xl">
        <div class="hph-container">
            <div class="hph-grid hph-grid-cols-2 hph-gap-2xl hph-items-center">
                
                <!-- Content -->
                <div class="hph-preapproval-content">
                    <div class="hph-section-number hph-display-1 hph-text-primary hph-font-bold hph-mb-md">01</div>
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
    
    <!-- Mortgage Calculator Section -->
    <section id="mortgage-calculator" class="hph-section hph-bg-gray-50 hph-py-3xl">
        <div class="hph-container">
            
            <div class="hph-section-header hph-text-center hph-mb-3xl">
                <div class="hph-badge hph-badge-primary hph-mb-lg">Mortgage Calculator</div>
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
                        
                    </form>
                </div>
                
                <!-- Results -->
                <div class="hph-calculator-results">
                    
                    <!-- Monthly Payment Card -->
                    <div class="hph-card hph-card-gradient hph-text-center hph-mb-xl">
                        <h3 class="hph-text-lg hph-text-white hph-opacity-90 hph-mb-md">Monthly Payment</h3>
                        <div id="monthly-payment" class="hph-display-1 hph-text-white hph-mb-sm">$2,234</div>
                        <p class="hph-text-white hph-opacity-80 hph-mb-0">Principal & Interest</p>
                    </div>
                    
                    <!-- Loan Summary -->
                    <div class="hph-card hph-bg-white">
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
                    <strong>Disclaimer:</strong> This calculator provides estimates only. Contact Remy for personalized quotes.
                </p>
            </div>
            
        </div>
    </section>
    
    <!-- Loan Types Section -->
    <?php
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'grid',
        'background' => 'white',
        'padding' => 'xl',
        'columns' => 3,
        'badge' => 'Loan Options',
        'headline' => 'Choose the Right Loan for You',
        'subheadline' => 'We work with multiple loan types to find the perfect fit for your situation',
        'animation' => true,
        'items' => array(
            array(
                'icon' => 'fas fa-home',
                'title' => 'Conventional Loans',
                'content' => 'Traditional financing with competitive rates. Perfect for buyers with good credit and stable income. Down payments as low as 3%.',
                'link' => array(
                    'text' => 'Learn More',
                    'url' => '#'
                )
            ),
            array(
                'icon' => 'fas fa-shield-alt',
                'title' => 'FHA Loans',
                'content' => 'Government-backed loans with lower down payment requirements. Ideal for first-time buyers with down payments as low as 3.5%.',
                'link' => array(
                    'text' => 'Learn More',
                    'url' => '#'
                )
            ),
            array(
                'icon' => 'fas fa-star',
                'title' => 'VA Loans',
                'content' => 'Exclusive benefits for veterans and service members. No down payment required, no private mortgage insurance needed.',
                'link' => array(
                    'text' => 'Learn More',
                    'url' => '#'
                )
            )
        ),
        'section_id' => 'loan-types'
    ));
    ?>
    
    <!-- Process Section -->
    <?php
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'two-column',
        'background' => 'light',
        'padding' => 'xl',
        'alignment' => 'left',
        'badge' => 'The Process',
        'headline' => 'Simple Steps to Homeownership',
        'subheadline' => 'We guide you through every step',
        'content' => '<ol style="padding-left: 1.5rem; line-height: 1.8;">
            <li style="margin-bottom: 1rem;"><strong>Get Pre-approved:</strong> Submit your application and receive your pre-approval letter within 24 hours.</li>
            <li style="margin-bottom: 1rem;"><strong>Shop for Homes:</strong> Armed with your pre-approval, start looking for your perfect home with confidence.</li>
            <li style="margin-bottom: 1rem;"><strong>Make an Offer:</strong> Your pre-approval letter shows sellers you\'re a serious buyer.</li>
            <li style="margin-bottom: 1rem;"><strong>Complete the Process:</strong> We handle the paperwork and coordinate with all parties to ensure a smooth closing.</li>
        </ol>',
        'buttons' => array(
            array(
                'text' => 'Start Your Pre-approval',
                'url' => '#mortgage-calculator',
                'style' => 'primary',
                'size' => 'lg',
                'icon' => 'fas fa-play'
            )
        ),
        'image' => array(
            'url' => function_exists('hph_get_image_url') ? hph_get_image_url('mortgage-process.jpg') : '',
            'alt' => 'Mortgage application process'
        ),
        'animation' => true,
        'section_id' => 'process'
    ));
    ?>
    
    <!-- Benefits Stats Section -->
    <?php
    get_template_part('template-parts/sections/content', null, array(
        'layout' => 'stats',
        'background' => 'primary',
        'padding' => 'xl',
        'badge' => 'Why Choose Us',
        'headline' => 'Trusted by Delaware Homebuyers',
        'subheadline' => 'Our track record speaks for itself',
        'animation' => true,
        'stats' => array(
            array(
                'number' => '500+',
                'label' => 'Loans Processed',
                'description' => 'Successfully closed in 2024'
            ),
            array(
                'number' => '24',
                'suffix' => 'hrs',
                'label' => 'Average Approval Time',
                'description' => 'Fast pre-approval process'
            ),
            array(
                'number' => '98%',
                'label' => 'Client Satisfaction',
                'description' => 'Would recommend to others'
            ),
            array(
                'number' => '15+',
                'suffix' => 'yrs',
                'label' => 'Combined Experience',
                'description' => 'In Delaware real estate'
            )
        ),
        'section_id' => 'stats'
    ));
    ?>
    
    <!-- Remy Haynes Contact Section -->
    <section id="remy-contact" class="hph-section hph-bg-gray-900 hph-text-white hph-py-3xl">
        <div class="hph-container">
            <div class="hph-grid hph-grid-cols-3 hph-gap-xl hph-items-center">
                
                <!-- Remy's Photo -->
                <div class="hph-remy-photo hph-text-center">
                    <div class="hph-avatar-xl hph-mx-auto hph-mb-md">
                        <?php if (function_exists('hph_get_image_url')) : ?>
                        <img src="<?php echo hph_get_image_url('remy-haynes.jpg'); ?>" 
                             alt="Remy Haynes" 
                             class="hph-rounded-full hph-w-full hph-h-full hph-object-cover">
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Remy's Info -->
                <div class="hph-remy-info hph-col-span-2">
                    <h2 class="hph-heading-xl hph-text-white hph-mb-md">Remy Haynes</h2>
                    <p class="hph-text-lg hph-text-gray-300 hph-mb-sm">Loan Officer | NMLS 2433656</p>
                    <p class="hph-text-base hph-text-gray-300 hph-mb-lg">
                        <a href="tel:302-504-3396" class="hph-text-primary hph-no-underline hover:hph-underline">302.504.3396</a>
                    </p>
                    <p class="hph-text-base hph-text-gray-300 hph-mb-xl">
                        <a href="mailto:rhaynes@mustachomortgages.com" class="hph-text-primary hph-no-underline hover:hph-underline">rhaynes@mustachomortgages.com</a>
                    </p>
                    
                    <!-- Mustache Mortgages Logo -->
                    <div class="hph-mustache-logo">
                        <?php if (function_exists('hph_get_image_url')) : ?>
                        <img src="<?php echo hph_get_image_url('mustache-mortgages-logo.png'); ?>" 
                             alt="Mustache Mortgages" 
                             class="hph-h-12 hph-w-auto">
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
            
            <!-- Disclaimer -->
            <div class="hph-disclaimer hph-mt-xl hph-pt-lg hph-border-t hph-border-gray-700">
                <p class="hph-text-xs hph-text-gray-400 hph-text-center">
                    Clients are not required to use or recommend Mustache Mortgage as a condition for the 
                    purchase, sale, or refinancing of the subject property. There are other mortgage lenders 
                    available with similar services and you are free to shop around to determine that you are 
                    receiving the best services and rates for your needs.
                </p>
            </div>
        </div>
    </section>
    
    <?php
    // ============================================
    // Simple FAQ Section
    // ============================================
    get_template_part('template-parts/sections/faq', null, array(
        'background' => 'light',
        'padding' => 'xl',
        'badge' => 'Mortgage FAQs',
        'headline' => 'Common Questions',
        'subheadline' => 'Get answers to frequently asked mortgage questions',
        'layout' => 'accordion',
        'columns' => 1,
        'faqs' => array(
            array(
                'question' => 'What credit score do I need for a mortgage?',
                'answer' => 'Credit requirements vary by loan type. Conventional loans typically require 620+, FHA loans accept 580+, and VA loans are more flexible. Higher scores qualify for better rates.'
            ),
            array(
                'question' => 'How much should I put down?',
                'answer' => 'Down payments vary by loan type: FHA requires 3.5%, conventional can go as low as 3%, and VA/USDA offer 0% down options. 20% down avoids PMI on conventional loans.'
            ),
            array(
                'question' => 'How long does pre-approval take?',
                'answer' => 'Pre-approval can often be completed within 24-48 hours with all required documents. Full approval and closing typically takes 30-45 days.'
            ),
            array(
                'question' => 'What documents do I need?',
                'answer' => 'You\'ll need recent pay stubs, W-2s, bank statements, tax returns (2 years), and employment verification. Self-employed borrowers need additional documentation.'
            )
        ),
        'section_id' => 'mortgage-faq'
    ));
    ?>

    <!-- Custom Styles -->
    <style>
    /* Calculator Specific Styles */
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
    
    .hph-summary-list {
        display: grid;
        gap: 0.75rem;
    }
    
    .hph-summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--hph-border-color-light);
    }
    
    .hph-summary-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .hph-avatar-xl {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
        .hph-grid-cols-2,
        .hph-grid-cols-3 {
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
        
        .hph-benefits-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .hph-col-span-2 {
            text-align: center;
        }
    }
    </style>

    <!-- Calculator JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('mortgage-calculator-form');
        const inputs = form.querySelectorAll('input, select');
        
        function calculateMortgage() {
            const homePrice = parseFloat(document.getElementById('home-price').value) || 400000;
            const downPayment = parseFloat(document.getElementById('down-payment').value) || 80000;
            const interestRate = parseFloat(document.getElementById('interest-rate').value) || 6.5;
            const loanTerm = parseFloat(document.getElementById('loan-term').value) || 30;
            
            const loanAmount = homePrice - downPayment;
            const monthlyRate = (interestRate / 100) / 12;
            const numPayments = loanTerm * 12;
            
            const monthlyPayment = (loanAmount * monthlyRate * Math.pow(1 + monthlyRate, numPayments)) / 
                                  (Math.pow(1 + monthlyRate, numPayments) - 1);
            
            const totalInterest = (monthlyPayment * numPayments) - loanAmount;
            const totalPayments = loanAmount + totalInterest;
            
            // Update display
            document.getElementById('monthly-payment').textContent = '$' + Math.round(monthlyPayment).toLocaleString();
            document.getElementById('loan-amount').textContent = '$' + Math.round(loanAmount).toLocaleString();
            document.getElementById('total-interest').textContent = '$' + Math.round(totalInterest).toLocaleString();
            document.getElementById('total-payments').textContent = '$' + Math.round(totalPayments).toLocaleString();
        }
        
        // Sync down payment dollar and percentage
        document.getElementById('down-payment').addEventListener('input', function() {
            const homePrice = parseFloat(document.getElementById('home-price').value) || 400000;
            const downPayment = parseFloat(this.value) || 0;
            const percentage = (downPayment / homePrice) * 100;
            document.getElementById('down-payment-percent').value = percentage.toFixed(1);
            calculateMortgage();
        });
        
        document.getElementById('down-payment-percent').addEventListener('input', function() {
            const homePrice = parseFloat(document.getElementById('home-price').value) || 400000;
            const percentage = parseFloat(this.value) || 0;
            const downPayment = (homePrice * percentage) / 100;
            document.getElementById('down-payment').value = Math.round(downPayment);
            calculateMortgage();
        });
        
        // Calculate on all input changes
        inputs.forEach(input => {
            input.addEventListener('input', calculateMortgage);
        });
        
        // Initial calculation
        calculateMortgage();
    });
    </script>

</main>

<?php get_footer(); ?>
