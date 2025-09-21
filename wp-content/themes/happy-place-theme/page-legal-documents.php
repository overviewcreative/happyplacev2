<?php
/**
 * Template Name: Legal Information & Policies
 * Description: Comprehensive legal documents and policies page for The Parker Group
 * 
 * @package TheParkerGroup
 * @since 1.0.0
 */

// Enqueue legal page specific CSS
wp_enqueue_style('legal-documents', get_template_directory_uri() . '/assets/css/pages/legal-documents.css', [], '1.0');

get_header(); ?>

<style>
    /* Legal Documents Page Styles */
    .legal-header {
        background: linear-gradient(135deg, var(--hph-primary) 0%, var(--hph-primary-dark) 100%);
        color: var(--hph-white);
        padding: var(--hph-padding-2xl);
        padding-top: calc(var(--hph-header-height) + var(--hph-space-8));
        box-shadow: var(--hph-shadow-xl);
    }

    @media (max-width: 768px) {
        .legal-header {
            padding-top: calc(var(--hph-header-height-mobile) + var(--hph-space-6));
        }
    }

    .legal-header h1 {
        font-size: var(--hph-text-3xl);
        font-weight: var(--hph-font-bold);
        margin-bottom: var(--hph-space-4);
        color: var(--hph-white);
    }

    .legal-header p {
        font-size: var(--hph-text);
        opacity: 0.95;
    }

    /* Navigation Tabs */
    .legal-nav {
        background: var(--hph-white);
        padding: var(--hph-space-4) 0;
        position: sticky;
        top: 0;
        z-index: var(--hph-z-sticky);
        box-shadow: var(--hph-shadow-md);
        margin-bottom: var(--hph-space-8);
    }

    .nav-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: var(--hph-gap-sm);
        justify-content: center;
    }

    .nav-tab {
        padding: var(--hph-space-2) var(--hph-space-6);
        background: var(--hph-gray-100);
        border: none;
        border-radius: var(--hph-radius-full);
        cursor: pointer;
        transition: var(--hph-transition-base);
        font-size: var(--hph-text-sm);
        font-weight: var(--hph-font-medium);
        color: var(--hph-text-muted);
    }

    .nav-tab:hover {
        background: var(--hph-primary);
        color: var(--hph-white);
        transform: translateY(-2px);
        box-shadow: var(--hph-shadow-lg);
    }

    .nav-tab.active {
        background: var(--hph-primary);
        color: var(--hph-white);
    }
    
    /* Content Sections */
    .legal-content-section {
        background: var(--hph-white);
        border-radius: var(--hph-radius-lg);
        padding: var(--hph-card-padding-lg);
        margin-bottom: var(--hph-space-8);
        box-shadow: var(--hph-shadow-xl);
        display: none;
    }

    .legal-content-section.active {
        display: block;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(var(--hph-spacing-md));
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .legal-section-header {
        border-bottom: 3px solid var(--hph-primary);
        padding-bottom: var(--hph-space-4);
        margin-bottom: var(--hph-space-8);
    }

    .legal-section-header h2 {
        color: var(--hph-text-color);
        font-size: var(--hph-text-2xl);
        font-weight: var(--hph-font-bold);
        margin-bottom: var(--hph-space-2);
    }

    .last-updated {
        color: var(--hph-text-light);
        font-size: var(--hph-text-sm);
        font-style: italic;
    }
    
    /* Typography within content */
    .legal-content-section h3 {
        color: var(--hph-text-color);
        margin: var(--hph-space-8) 0 var(--hph-space-4);
        font-size: var(--hph-text-xl);
        font-weight: var(--hph-font-semibold);
        border-left: 4px solid var(--hph-primary);
        padding-left: var(--hph-space-4);
    }

    .legal-content-section h4 {
        color: var(--hph-text-muted);
        margin: var(--hph-space-6) 0 var(--hph-space-2);
        font-size: var(--hph-text-lg);
        font-weight: var(--hph-font-medium);
    }

    .legal-content-section p {
        margin-bottom: var(--hph-paragraph-margin);
        line-height: var(--hph-leading-relaxed);
        color: var(--hph-text-muted);
    }

    .legal-content-section ul, .legal-content-section ol {
        margin: var(--hph-space-4) 0 var(--hph-space-4) var(--hph-space-8);
        color: var(--hph-text-muted);
    }

    .legal-content-section li {
        margin-bottom: var(--hph-space-2);
        line-height: var(--hph-leading-relaxed);
    }

    /* Highlight Boxes */
    .legal-highlight-box {
        background: linear-gradient(135deg, rgba(var(--hph-primary-rgb), 0.05) 0%, rgba(var(--hph-primary-rgb), 0.1) 100%);
        border-left: 4px solid var(--hph-primary);
        padding: var(--hph-space-6);
        margin: var(--hph-space-8) 0;
        border-radius: var(--hph-radius-md);
    }

    .legal-warning-box {
        background: linear-gradient(135deg, rgba(232, 168, 124, 0.1) 0%, rgba(232, 168, 124, 0.15) 100%);
        border-left: 4px solid var(--hph-warning);
        padding: var(--hph-space-6);
        margin: var(--hph-space-8) 0;
        border-radius: var(--hph-radius-md);
    }

    /* Contact Information */
    .legal-contact-info {
        background: var(--hph-gray-50);
        padding: var(--hph-space-8);
        border-radius: var(--hph-radius-lg);
        margin-top: var(--hph-space-8);
    }

    .legal-contact-info h4 {
        color: var(--hph-primary-dark);
        margin-bottom: var(--hph-space-4);
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .legal-content-section {
            padding: var(--hph-card-padding);
        }

        .legal-header h1 {
            font-size: var(--hph-text-xl);
        }

        .nav-tabs {
            gap: var(--hph-gap-xs);
        }

        .nav-tab {
            padding: var(--hph-space-1) var(--hph-space-4);
            font-size: var(--hph-text-xs);
        }
    }

    /* Print Styles */
    @media print {
        .legal-nav {
            display: none;
        }

        .legal-content-section {
            display: block !important;
            page-break-after: always;
            box-shadow: none;
            border: 1px solid var(--hph-gray-300);
        }
    }
</style>

<!-- Skip to main content for accessibility -->
<a href="#legal-main-content" class="skip-link screen-reader-text"><?php esc_html_e( 'Skip to main content', 'parker-group' ); ?></a>

<!-- Header -->
<header class="legal-header">
    <div class="hph-container">
        <h1><?php esc_html_e( 'Legal Information & Policies', 'parker-group' ); ?></h1>
        <p><?php esc_html_e( 'The Parker Group - Transparency, Compliance, and Protection', 'parker-group' ); ?></p>
    </div>
</header>

<!-- Navigation -->
<nav class="legal-nav" role="navigation" aria-label="<?php esc_attr_e( 'Legal document navigation', 'parker-group' ); ?>">
    <div class="hph-container">
        <div class="nav-tabs">
            <button class="nav-tab active" onclick="showLegalSection('transparency')" aria-label="<?php esc_attr_e( 'View Transparency and Disclosure', 'parker-group' ); ?>">
                <?php esc_html_e( 'Transparency & Disclosure', 'parker-group' ); ?>
            </button>
            <button class="nav-tab" onclick="showLegalSection('privacy')" aria-label="<?php esc_attr_e( 'View Privacy Policy', 'parker-group' ); ?>">
                <?php esc_html_e( 'Privacy Policy', 'parker-group' ); ?>
            </button>
            <button class="nav-tab" onclick="showLegalSection('terms')" aria-label="<?php esc_attr_e( 'View Terms of Service', 'parker-group' ); ?>">
                <?php esc_html_e( 'Terms of Service', 'parker-group' ); ?>
            </button>
            <button class="nav-tab" onclick="showLegalSection('fair-housing')" aria-label="<?php esc_attr_e( 'View Fair Housing', 'parker-group' ); ?>">
                <?php esc_html_e( 'Fair Housing', 'parker-group' ); ?>
            </button>
            <button class="nav-tab" onclick="showLegalSection('accessibility')" aria-label="<?php esc_attr_e( 'View Accessibility', 'parker-group' ); ?>">
                <?php esc_html_e( 'Accessibility', 'parker-group' ); ?>
            </button>
            <button class="nav-tab" onclick="showLegalSection('state-disclosures')" aria-label="<?php esc_attr_e( 'View State Disclosures', 'parker-group' ); ?>">
                <?php esc_html_e( 'State Disclosures', 'parker-group' ); ?>
            </button>
            <button class="nav-tab" onclick="showLegalSection('dmca')" aria-label="<?php esc_attr_e( 'View DMCA Policy', 'parker-group' ); ?>">
                <?php esc_html_e( 'DMCA Policy', 'parker-group' ); ?>
            </button>
            <button class="nav-tab" onclick="showLegalSection('cookies')" aria-label="<?php esc_attr_e( 'View Cookie Policy', 'parker-group' ); ?>">
                <?php esc_html_e( 'Cookie Policy', 'parker-group' ); ?>
            </button>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main id="legal-main-content" class="hph-container">
    
    <!-- Transparency and Disclosure Section -->
    <section id="transparency" class="legal-content-section active">
        <div class="legal-section-header">
            <h2><?php esc_html_e( 'Transparency and Disclosure in Client Interactions', 'parker-group' ); ?></h2>
            <p class="last-updated"><?php echo esc_html( sprintf( __( 'Last updated: %s', 'parker-group' ), 'August 8, 2024' ) ); ?></p>
        </div>
        
        <p><?php esc_html_e( 'At The Parker Group, we prioritize transparency, clarity, and full disclosure in our interactions with clients and throughout the real estate process. In compliance with legal requirements and to ensure our clients are fully informed, we have instituted the following practices and disclosures:', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( '1. Agency and Brokerage Compensation', 'parker-group' ); ?></h3>
        <div class="legal-highlight-box">
            <h4><?php esc_html_e( '1.1 Compensation Disclosure', 'parker-group' ); ?></h4>
            <ul>
                <li><?php esc_html_e( 'We explicitly do not require listing brokers or sellers to make offers of compensation to buyer brokers or other buyer representatives.', 'parker-group' ); ?></li>
                <li><?php esc_html_e( 'Any offers of compensation made are neither blanket, unconditional, nor unilateral.', 'parker-group' ); ?></li>
                <li><?php esc_html_e( 'We strictly prohibit the disclosure of listing broker compensation or total broker compensation on the Multiple Listing Service (MLS) or third-party sites.', 'parker-group' ); ?></li>
            </ul>
        </div>
        
        <h4><?php esc_html_e( '1.2 Written Agreements', 'parker-group' ); ?></h4>
        <ul>
            <li><?php esc_html_e( 'All clients engaging with a buyer\'s agent must enter into a detailed written agreement prior to touring any properties.', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'This agreement will explicitly specify the exact amount or rate of compensation the buyer\'s agent will receive.', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'The compensation amount must be clearly defined and objectively ascertainable (e.g., "buyer broker compensation shall be a fixed percentage of the purchase price or a specified dollar amount").', 'parker-group' ); ?></li>
        </ul>
        
        <h4><?php esc_html_e( '1.3 Cost and Service Representation', 'parker-group' ); ?></h4>
        <p><?php esc_html_e( 'Our brokerage services are never represented as "free" or available at no cost unless we receive no financial compensation from any source for those services. We ensure that all clients are fully aware of any compensation we receive.', 'parker-group' ); ?></p>
        
        <h4><?php esc_html_e( '1.4 Seller and Buyer Disclosures', 'parker-group' ); ?></h4>
        <ul>
            <li><?php esc_html_e( 'We mandate explicit, written disclosure to sellers of any payment or offer of payment to buyer brokers before any such agreement is executed.', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Broker commissions are not set by law and are fully negotiable.', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Clients must acknowledge this disclosure in writing.', 'parker-group' ); ?></li>
        </ul>
        
        <h4><?php esc_html_e( '1.5 No Filtering Based on Compensation', 'parker-group' ); ?></h4>
        <p><?php esc_html_e( 'We do not filter or restrict MLS listings based on the existence or level of compensation offered to buyer brokers. All available listings are presented to clients regardless of the compensation structure.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( '2. Ethical Standards and Compliance', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'The Parker Group upholds the highest ethical standards in accordance with:', 'parker-group' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'The Code of Ethics by the National Association of REALTORS® (NAR)', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'The Delaware Real Estate Commission requirements', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'The Maryland Real Estate Commission requirements', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Our own internal code of ethics', 'parker-group' ); ?></li>
        </ul>
        
        <h3><?php esc_html_e( '3. Legal Notice and Disclaimer', 'parker-group' ); ?></h3>
        <div class="legal-warning-box">
            <p><strong><?php esc_html_e( 'Important:', 'parker-group' ); ?></strong> <?php esc_html_e( 'The information provided on this website and in all communications is for general informational purposes only and does not constitute legal, financial, tax, or real estate advice.', 'parker-group' ); ?></p>
            <p><?php esc_html_e( 'Users are strongly advised to consult with their own legal, financial, tax, and real estate professionals before making any decisions.', 'parker-group' ); ?></p>
        </div>
        
        <h3><?php esc_html_e( '4. Limitation of Liability', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'To the fullest extent permitted by applicable law, The Parker Group, its affiliates, agents, employees, officers, directors, or partners shall not be liable for any direct, indirect, incidental, consequential, special, exemplary, or punitive damages.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( '5. Settlement Compliance', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'The Parker Group adheres to the terms and conditions stipulated in the settlement agreement between the National Association of REALTORS® and plaintiffs in the relevant litigation. We implement all mandated practice changes and fully cooperate with all legal and regulatory requirements.', 'parker-group' ); ?></p>
        
        <div class="legal-contact-info">
            <h4><?php esc_html_e( 'Contact Information', 'parker-group' ); ?></h4>
            <p><strong><?php esc_html_e( 'The Parker Group', 'parker-group' ); ?></strong><br>
            <?php esc_html_e( '673 N. Bedford Street', 'parker-group' ); ?><br>
            <?php esc_html_e( 'Georgetown, DE 19947', 'parker-group' ); ?><br>
            <?php esc_html_e( 'Phone:', 'parker-group' ); ?> <a href="tel:302-217-6692">302-217-6692</a><br>
            <?php esc_html_e( 'Email:', 'parker-group' ); ?> <a href="mailto:cheers@theparkergroup.com">cheers@theparkergroup.com</a></p>
        </div>
    </section>
    
    <!-- Privacy Policy Section -->
    <section id="privacy" class="legal-content-section">
        <div class="legal-section-header">
            <h2><?php esc_html_e( 'Privacy Policy', 'parker-group' ); ?></h2>
            <p class="last-updated"><?php echo esc_html( sprintf( __( 'Last updated: %s', 'parker-group' ), 'August 8, 2024' ) ); ?></p>
        </div>
        
        <h3><?php esc_html_e( '1. Introduction', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'At The Parker Group, we are committed to protecting the privacy and security of your personal information. This Privacy Notice describes our policies and procedures on the collection, use, disclosure, and protection of your information.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( '2. Information We Collect', 'parker-group' ); ?></h3>
        <h4><?php esc_html_e( 'Personal Information', 'parker-group' ); ?></h4>
        <ul>
            <li><?php esc_html_e( 'Name, email address, phone number, postal address', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Transaction information and property details', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Financial information related to real estate transactions', 'parker-group' ); ?></li>
        </ul>
        
        <h4><?php esc_html_e( 'Usage Information', 'parker-group' ); ?></h4>
        <ul>
            <li><?php esc_html_e( 'IP address and browser type', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Pages visited and interaction patterns', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Date and time of visits', 'parker-group' ); ?></li>
        </ul>
        
        <h3><?php esc_html_e( '3. How We Use Your Information', 'parker-group' ); ?></h3>
        <ul>
            <li><strong><?php esc_html_e( 'Providing Services:', 'parker-group' ); ?></strong> <?php esc_html_e( 'To manage our real estate services and process transactions', 'parker-group' ); ?></li>
            <li><strong><?php esc_html_e( 'Communication:', 'parker-group' ); ?></strong> <?php esc_html_e( 'To respond to inquiries and provide customer support', 'parker-group' ); ?></li>
            <li><strong><?php esc_html_e( 'Improvement:', 'parker-group' ); ?></strong> <?php esc_html_e( 'To enhance our website and services', 'parker-group' ); ?></li>
            <li><strong><?php esc_html_e( 'Legal Compliance:', 'parker-group' ); ?></strong> <?php esc_html_e( 'To comply with legal obligations and resolve disputes', 'parker-group' ); ?></li>
        </ul>
        
        <div class="legal-highlight-box">
            <p><strong><?php esc_html_e( 'Note:', 'parker-group' ); ?></strong> <?php esc_html_e( 'Text messaging originator opt-in data and consent will not be shared with any third parties.', 'parker-group' ); ?></p>
        </div>
        
        <h3><?php esc_html_e( '4. Your Rights', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'You have certain rights regarding your personal information:', 'parker-group' ); ?></p>
        <ul>
            <li><strong><?php esc_html_e( 'Access:', 'parker-group' ); ?></strong> <?php esc_html_e( 'Request access to your personal information', 'parker-group' ); ?></li>
            <li><strong><?php esc_html_e( 'Correction:', 'parker-group' ); ?></strong> <?php esc_html_e( 'Request corrections to any inaccuracies', 'parker-group' ); ?></li>
            <li><strong><?php esc_html_e( 'Deletion:', 'parker-group' ); ?></strong> <?php esc_html_e( 'Request deletion of your personal information', 'parker-group' ); ?></li>
            <li><strong><?php esc_html_e( 'Opt-Out:', 'parker-group' ); ?></strong> <?php esc_html_e( 'Opt-out of marketing communications at any time', 'parker-group' ); ?></li>
        </ul>
        
        <h3><?php esc_html_e( '5. Security', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'We take reasonable measures to protect your personal information from unauthorized access, disclosure, alteration, and destruction. However, no security system is impenetrable, and we cannot guarantee absolute security.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( '6. Contact Us', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'If you have any questions about this Privacy Policy, please contact us using the information provided above.', 'parker-group' ); ?></p>
    </section>
    
    <!-- Terms of Service Section -->
    <section id="terms" class="legal-content-section">
        <div class="legal-section-header">
            <h2><?php esc_html_e( 'Terms of Service', 'parker-group' ); ?></h2>
            <p class="last-updated"><?php echo esc_html( sprintf( __( 'Last updated: %s', 'parker-group' ), 'August 8, 2024' ) ); ?></p>
        </div>
        
        <h3><?php esc_html_e( '1. Acceptance of Terms', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'By accessing and using The Parker Group website and services, you accept and agree to be bound by these Terms of Service and all applicable laws and regulations.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( '2. Use of Services', 'parker-group' ); ?></h3>
        <h4><?php esc_html_e( 'Permitted Use', 'parker-group' ); ?></h4>
        <ul>
            <li><?php esc_html_e( 'You may use our services for lawful purposes only', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'You agree not to use our services to violate any applicable laws or regulations', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'You will not attempt to gain unauthorized access to any portion of our website', 'parker-group' ); ?></li>
        </ul>
        
        <h3><?php esc_html_e( '3. Intellectual Property', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'All content on this website, including text, graphics, logos, and images, is the property of The Parker Group or its content suppliers and is protected by copyright and other intellectual property laws.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( '4. Indemnification', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'You agree to indemnify, defend, and hold harmless The Parker Group from and against all losses, expenses, damages, and costs resulting from any violation of these terms.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( '5. Governing Law', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'These Terms shall be governed by and construed in accordance with the laws of the State of Delaware, without regard to its conflict of law principles.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( '6. Arbitration Agreement', 'parker-group' ); ?></h3>
        <div class="legal-warning-box">
            <p><?php esc_html_e( 'Any dispute arising out of or relating to our services shall be determined by arbitration in Delaware before one arbitrator. The arbitration shall be administered by JAMS pursuant to its Comprehensive Arbitration Rules and Procedures.', 'parker-group' ); ?></p>
        </div>
    </section>
    
    <!-- Fair Housing Section -->
    <section id="fair-housing" class="legal-content-section">
        <div class="legal-section-header">
            <h2><?php esc_html_e( 'Fair Housing Statement', 'parker-group' ); ?></h2>
            <p class="last-updated"><?php echo esc_html( sprintf( __( 'Last updated: %s', 'parker-group' ), 'August 8, 2024' ) ); ?></p>
        </div>
        
        <h3><?php esc_html_e( 'Our Commitment to Fair Housing', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'The Parker Group is committed to the letter and spirit of the Fair Housing Act and all applicable fair housing laws. We provide equal professional services without regard to race, color, religion, sex, disability, familial status, national origin, sexual orientation, gender identity, or any other protected class.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( 'Fair Housing Act', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'The Fair Housing Act prohibits discrimination in housing based on:', 'parker-group' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'Race or color', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'National origin', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Religion', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Sex (including sexual orientation and gender identity)', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Familial status (including children under 18 living with parents or legal custodians)', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Disability (physical or mental)', 'parker-group' ); ?></li>
        </ul>
        
        <h3><?php esc_html_e( 'Additional Protected Classes', 'parker-group' ); ?></h3>
        <h4><?php esc_html_e( 'Delaware Protected Classes:', 'parker-group' ); ?></h4>
        <ul>
            <li><?php esc_html_e( 'Age', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Marital status', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Source of income', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Sexual orientation', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Gender identity', 'parker-group' ); ?></li>
        </ul>
        
        <h4><?php esc_html_e( 'Maryland Protected Classes:', 'parker-group' ); ?></h4>
        <ul>
            <li><?php esc_html_e( 'Marital status', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Sexual orientation', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Gender identity', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Source of income', 'parker-group' ); ?></li>
        </ul>
        
        <h3><?php esc_html_e( 'Reasonable Accommodations', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'We are committed to providing reasonable accommodations to persons with disabilities. This includes:', 'parker-group' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'Making reasonable modifications to policies and procedures', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Providing auxiliary aids and services for effective communication', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Ensuring accessibility of our services and facilities', 'parker-group' ); ?></li>
        </ul>
        
        <h3><?php esc_html_e( 'Filing a Complaint', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'If you believe you have experienced discrimination, you may file a complaint with:', 'parker-group' ); ?></p>
        <div class="legal-highlight-box">
            <p><strong><?php esc_html_e( 'U.S. Department of Housing and Urban Development (HUD)', 'parker-group' ); ?></strong><br>
            <?php esc_html_e( 'Phone: 1-800-669-9777', 'parker-group' ); ?><br>
            <?php esc_html_e( 'TTY: 1-800-927-9275', 'parker-group' ); ?><br>
            <?php esc_html_e( 'Website:', 'parker-group' ); ?> <a href="https://www.hud.gov" target="_blank" rel="noopener">www.hud.gov</a></p>
            
            <p><strong><?php esc_html_e( 'Delaware Division of Human Relations', 'parker-group' ); ?></strong><br>
            <?php esc_html_e( 'Phone: (302) 761-8200', 'parker-group' ); ?></p>
            
            <p><strong><?php esc_html_e( 'Maryland Commission on Civil Rights', 'parker-group' ); ?></strong><br>
            <?php esc_html_e( 'Phone: (410) 767-8600', 'parker-group' ); ?></p>
        </div>
    </section>
    
    <!-- Accessibility Section -->
    <section id="accessibility" class="legal-content-section">
        <div class="legal-section-header">
            <h2><?php esc_html_e( 'Accessibility Statement', 'parker-group' ); ?></h2>
            <p class="last-updated"><?php echo esc_html( sprintf( __( 'Last updated: %s', 'parker-group' ), 'August 8, 2024' ) ); ?></p>
        </div>
        
        <h3><?php esc_html_e( 'Our Commitment to Accessibility', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'The Parker Group is committed to ensuring digital accessibility for people with disabilities. We are continually improving the user experience for everyone and applying the relevant accessibility standards.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( 'Conformance Status', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'We aim to conform to the Web Content Accessibility Guidelines (WCAG) 2.1 Level AA standards. These guidelines explain how to make web content more accessible for people with disabilities.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( 'Accessibility Features', 'parker-group' ); ?></h3>
        <ul>
            <li><?php esc_html_e( 'Alternative text for images', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Keyboard navigation support', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Clear heading structure', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Sufficient color contrast', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Resizable text without loss of functionality', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Descriptive link text', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Form labels and instructions', 'parker-group' ); ?></li>
        </ul>
        
        <h3><?php esc_html_e( 'Ongoing Efforts', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'We work to maintain and improve accessibility through:', 'parker-group' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'Regular accessibility audits', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Staff training on accessibility best practices', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Integration of accessibility into our development process', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'User feedback and testing', 'parker-group' ); ?></li>
        </ul>
        
        <h3><?php esc_html_e( 'Contact Us About Accessibility', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'We welcome your feedback on the accessibility of our website. Please let us know if you encounter accessibility barriers:', 'parker-group' ); ?></p>
        <div class="legal-contact-info">
            <p><?php esc_html_e( 'Email:', 'parker-group' ); ?> <a href="mailto:cheers@theparkergroup.com">cheers@theparkergroup.com</a><br>
            <?php esc_html_e( 'Phone:', 'parker-group' ); ?> <a href="tel:302-217-6692">302-217-6692</a><br>
            <?php esc_html_e( 'We will respond to accessibility feedback within 5 business days.', 'parker-group' ); ?></p>
        </div>
    </section>
    
    <!-- State-Specific Disclosures Section -->
    <section id="state-disclosures" class="legal-content-section">
        <div class="legal-section-header">
            <h2><?php esc_html_e( 'State-Specific Disclosures', 'parker-group' ); ?></h2>
            <p class="last-updated"><?php echo esc_html( sprintf( __( 'Last updated: %s', 'parker-group' ), 'August 8, 2024' ) ); ?></p>
        </div>
        
        <h3><?php esc_html_e( 'Delaware Real Estate Disclosures', 'parker-group' ); ?></h3>
        
        <h4><?php esc_html_e( 'Seller Property Disclosure', 'parker-group' ); ?></h4>
        <p><?php esc_html_e( 'Delaware law requires sellers of residential real property to provide written disclosure of all material defects known at the time the property is listed for sale (25 Del. C. § 2572).', 'parker-group' ); ?></p>
        
        <h4><?php esc_html_e( 'Delaware Real Estate Commission', 'parker-group' ); ?></h4>
        <div class="legal-highlight-box">
            <p><strong><?php esc_html_e( 'License Information:', 'parker-group' ); ?></strong><br>
            <?php esc_html_e( 'The Parker Group is licensed by the Delaware Real Estate Commission', 'parker-group' ); ?><br>
            <?php esc_html_e( 'License verification available at:', 'parker-group' ); ?> <a href="https://dpr.delaware.gov" target="_blank" rel="noopener"><?php esc_html_e( 'Delaware Division of Professional Regulation', 'parker-group' ); ?></a></p>
        </div>
        
        <h4><?php esc_html_e( 'Delaware Real Estate Guaranty Fund', 'parker-group' ); ?></h4>
        <p><?php esc_html_e( 'The Delaware Real Estate Commission maintains a Real Estate Guaranty Fund for the benefit of consumers who suffer monetary damages due to wrongful acts by licensed real estate professionals.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( 'Maryland Real Estate Disclosures', 'parker-group' ); ?></h3>
        
        <h4><?php esc_html_e( 'Maryland Residential Property Disclosure', 'parker-group' ); ?></h4>
        <p><?php esc_html_e( 'Maryland law (Md. Code § 10-702) requires sellers to either:', 'parker-group' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'Complete a Residential Property Disclosure Statement detailing the condition of the property, OR', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Provide a Disclaimer Statement selling the property "as is" (but must still disclose known latent defects)', 'parker-group' ); ?></li>
        </ul>
        
        <h4><?php esc_html_e( 'Maryland Real Estate Commission', 'parker-group' ); ?></h4>
        <div class="legal-highlight-box">
            <p><strong><?php esc_html_e( 'License Information:', 'parker-group' ); ?></strong><br>
            <?php esc_html_e( 'The Parker Group maintains licenses with the Maryland Real Estate Commission', 'parker-group' ); ?><br>
            <?php esc_html_e( '100 S. Charles Street, Tower 1, Baltimore, MD 21201', 'parker-group' ); ?><br>
            <?php esc_html_e( 'License verification available at:', 'parker-group' ); ?> <a href="https://labor.maryland.gov/license/mrec/" target="_blank" rel="noopener"><?php esc_html_e( 'Maryland Real Estate Commission', 'parker-group' ); ?></a></p>
        </div>
        
        <h4><?php esc_html_e( 'Lead-Based Paint Disclosure (Federal & State)', 'parker-group' ); ?></h4>
        <p><?php esc_html_e( 'For homes built before 1978, federal law requires:', 'parker-group' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'Disclosure of known lead-based paint hazards', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Provision of EPA pamphlet "Protect Your Family From Lead in Your Home"', 'parker-group' ); ?></li>
            <li><?php esc_html_e( '10-day period for buyer to conduct lead inspection', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Lead Warning Statement in sales contract', 'parker-group' ); ?></li>
        </ul>
        
        <h3><?php esc_html_e( 'RESPA Compliance', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'The Parker Group complies with the Real Estate Settlement Procedures Act (RESPA), which requires:', 'parker-group' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'Provision of Good Faith Estimates when applicable', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Disclosure of any affiliated business arrangements', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Prohibition of kickbacks and unearned fees', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Proper escrow account management', 'parker-group' ); ?></li>
        </ul>
    </section>
    
    <!-- DMCA Policy Section -->
    <section id="dmca" class="legal-content-section">
        <div class="legal-section-header">
            <h2><?php esc_html_e( 'DMCA Copyright Policy', 'parker-group' ); ?></h2>
            <p class="last-updated"><?php echo esc_html( sprintf( __( 'Last updated: %s', 'parker-group' ), 'August 8, 2024' ) ); ?></p>
        </div>
        
        <h3><?php esc_html_e( 'Digital Millennium Copyright Act Notice', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'The Parker Group respects the intellectual property rights of others and expects users of our website to do the same. In accordance with the Digital Millennium Copyright Act (DMCA), we will respond to notices of alleged copyright infringement that comply with the DMCA.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( 'Filing a DMCA Notice', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'If you believe that content on our website infringes your copyright, please provide our Copyright Agent with the following information:', 'parker-group' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'A physical or electronic signature of the copyright owner or authorized representative', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Identification of the copyrighted work claimed to be infringed', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Identification of the material that is claimed to be infringing and its location on our website', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Your contact information (address, telephone number, and email)', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'A statement that you have a good faith belief that the disputed use is not authorized', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'A statement made under penalty of perjury that the information is accurate', 'parker-group' ); ?></li>
        </ul>
        
        <h3><?php esc_html_e( 'Counter-Notice', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'If you believe that material you posted was wrongly removed, you may file a counter-notice with our Copyright Agent containing:', 'parker-group' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'Your physical or electronic signature', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Identification of the material that was removed', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'A statement under penalty of perjury that you have a good faith belief the material was removed by mistake', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Your name, address, phone number, and consent to jurisdiction', 'parker-group' ); ?></li>
        </ul>
        
        <h3><?php esc_html_e( 'Designated Copyright Agent', 'parker-group' ); ?></h3>
        <div class="legal-contact-info">
            <p><strong><?php esc_html_e( 'The Parker Group Copyright Agent', 'parker-group' ); ?></strong><br>
            <?php esc_html_e( 'Email:', 'parker-group' ); ?> <a href="mailto:copyright@theparkergroup.com">copyright@theparkergroup.com</a><br>
            <?php esc_html_e( 'Address: 673 N. Bedford Street, Georgetown, DE 19947', 'parker-group' ); ?></p>
        </div>
    </section>
    
    <!-- Cookie Policy Section -->
    <section id="cookies" class="legal-content-section">
        <div class="legal-section-header">
            <h2><?php esc_html_e( 'Cookie Policy', 'parker-group' ); ?></h2>
            <p class="last-updated"><?php echo esc_html( sprintf( __( 'Last updated: %s', 'parker-group' ), 'August 8, 2024' ) ); ?></p>
        </div>
        
        <h3><?php esc_html_e( 'What Are Cookies?', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'Cookies are small text files that are placed on your device when you visit our website. They help us provide you with a better experience by remembering your preferences and understanding how you use our site.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( 'Types of Cookies We Use', 'parker-group' ); ?></h3>
        
        <h4><?php esc_html_e( 'Essential Cookies', 'parker-group' ); ?></h4>
        <p><?php esc_html_e( 'These cookies are necessary for the website to function properly. They enable basic functions like page navigation and access to secure areas of the website.', 'parker-group' ); ?></p>
        
        <h4><?php esc_html_e( 'Analytics Cookies', 'parker-group' ); ?></h4>
        <p><?php esc_html_e( 'We use analytics cookies to understand how visitors interact with our website. This helps us improve our website and services.', 'parker-group' ); ?></p>
        
        <h4><?php esc_html_e( 'Functionality Cookies', 'parker-group' ); ?></h4>
        <p><?php esc_html_e( 'These cookies remember choices you make (such as language preferences) and provide enhanced, personalized features.', 'parker-group' ); ?></p>
        
        <h4><?php esc_html_e( 'Marketing Cookies', 'parker-group' ); ?></h4>
        <p><?php esc_html_e( 'Marketing cookies track your online activity to help us deliver more relevant advertising or limit how many times you see an ad.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( 'Managing Cookies', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'You can control and manage cookies through your browser settings. Please note that removing or blocking cookies may impact your user experience and parts of our website may no longer be fully accessible.', 'parker-group' ); ?></p>
        
        <h4><?php esc_html_e( 'Browser Controls', 'parker-group' ); ?></h4>
        <p><?php esc_html_e( 'Most browsers allow you to:', 'parker-group' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'See what cookies you have and delete them individually', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Block third-party cookies', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Block cookies from particular sites', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Block all cookies', 'parker-group' ); ?></li>
            <li><?php esc_html_e( 'Delete all cookies when you close your browser', 'parker-group' ); ?></li>
        </ul>
        
        <h3><?php esc_html_e( 'Changes to This Policy', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'We may update this Cookie Policy from time to time. Any changes will be posted on this page with an updated revision date.', 'parker-group' ); ?></p>
        
        <h3><?php esc_html_e( 'Contact Us', 'parker-group' ); ?></h3>
        <p><?php esc_html_e( 'If you have questions about our use of cookies, please contact us using the information provided above.', 'parker-group' ); ?></p>
    </section>
    
</main>

<!-- JavaScript for tab functionality -->
<script>
function showLegalSection(sectionId) {
    // Hide all sections
    const sections = document.querySelectorAll('.legal-content-section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    
    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.nav-tab');
    tabs.forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected section
    document.getElementById(sectionId).classList.add('active');
    
    // Add active class to clicked tab
    event.target.classList.add('active');
    
    // Scroll to top of content
    window.scrollTo({ top: document.querySelector('.legal-nav').offsetTop, behavior: 'smooth' });
    
    // Update URL hash without jumping
    history.pushState(null, null, '#' + sectionId);
}

// Handle direct navigation via URL hash
window.addEventListener('load', function() {
    const hash = window.location.hash.substring(1);
    if (hash && document.getElementById(hash)) {
        // Find and click the corresponding tab
        const tabs = document.querySelectorAll('.nav-tab');
        tabs.forEach(tab => {
            if (tab.textContent.toLowerCase().includes(hash.replace('-', ' '))) {
                tab.click();
            }
        });
    }
});

// Print functionality
window.addEventListener('beforeprint', function() {
    // Show all sections for printing
    const sections = document.querySelectorAll('.legal-content-section');
    sections.forEach(section => {
        section.style.display = 'block';
    });
});

window.addEventListener('afterprint', function() {
    // Restore original display
    const sections = document.querySelectorAll('.legal-content-section');
    sections.forEach(section => {
        if (!section.classList.contains('active')) {
            section.style.display = 'none';
        }
    });
});
</script>

<?php get_footer(); ?>