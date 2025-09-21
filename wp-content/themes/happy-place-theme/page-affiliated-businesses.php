<?php
/**
 * Template Name: Affiliated Businesses
 * 
 * Information about The Parker Group's affiliated business relationships
 * 
 * @package HappyPlaceTheme
 */

get_header();

// Hero Section - Professional introduction
$hero_config = array(
    'style' => 'gradient',
    'theme' => 'primary',
    'height' => 'md',
    'alignment' => 'center',
    'headline' => 'Affiliated Business Arrangement Disclosure',
    'subheadline' => 'Transparency in Our Business Relationships',
    'content' => 'The Parker Group is committed to providing full disclosure of our business relationships to ensure you can make informed decisions about the services you choose.',
    'fade_in' => true,
    'is_top_of_page' => true
);

get_template_part('template-parts/sections/hero', null, $hero_config);

// Main Content Section - Disclosure Information
$disclosure_content = array(
    'layout' => 'centered',
    'background' => 'white',
    'padding' => '3xl',
    'content_width' => 'narrow',
    'headline' => 'Important Disclosure Information',
    'headline_tag' => 'h2',
    'content' => '<div style="line-height: var(--hph-leading-relaxed); font-size: var(--hph-text-base);">
        <p style="margin-bottom: var(--hph-space-6);">The Real Estate Settlement and Procedures Act (RESPA) requires lenders, mortgage brokers, and servicers of home loans to provide borrowers with pertinent and timely disclosures regarding the nature and costs of the real estate settlement process.</p>
        
        <p style="margin-bottom: var(--hph-space-6);">RESPA also prohibits specific practices, such as kickbacks, and places limitations upon the use of escrow accounts. The Parker Group Real Estate wants to ensure that you are aware of any business relationships that we may have with other service providers and the financial benefit we may receive from such relationships.</p>
        
        <div style="background: var(--hph-primary-light); padding: var(--hph-space-8); border-radius: var(--hph-radius-lg); margin: var(--hph-margin-2xl) 0; border-left: 4px solid var(--hph-primary);">
            <h3 style="color: var(--hph-primary); margin: 0 0 var(--hph-space-4) 0; font-size: var(--hph-text-xl);">Affiliated Business Arrangements</h3>
            <p style="margin: 0; color: var(--hph-gray-700);">Sometimes, several businesses that offer settlement services are owned or controlled by a common corporate parent. These businesses are known as "affiliates." A lender, title insurance company, mortgage broker, real estate broker or other settlement service provider refers you to an affiliate for a settlement service and has either an affiliate relationship with such provider or a direct or indirect interest of more than 1% in such provider.</p>
        </div>
        
        <h3 style="color: var(--hph-gray-900); margin: var(--hph-margin-2xl) 0 var(--hph-space-6) 0; font-size: var(--hph-text-2xl);">Our Affiliated Business Partners</h3>
        
        <p style="margin-bottom: var(--hph-space-8);">The Parker Group currently has affiliated business relationships with the following companies:</p>
    </div>'
);

get_template_part('template-parts/sections/content', null, $disclosure_content);

// Affiliated Businesses Cards
$affiliated_businesses = array(
    'layout' => 'grid',
    'background' => 'light',
    'padding' => '3xl',
    'columns' => 2,
    'gap' => 'normal',
    'items' => array(
        array(
            'icon' => 'fas fa-shield-alt',
            'title' => 'Canopy Insurance Group',
            'content' => '<div>
                <p style="margin-bottom: var(--hph-space-4);"><strong>Insurance Services</strong></p>
                <p style="margin-bottom: var(--hph-space-4);">Canopy Insurance Group provides comprehensive insurance solutions including homeowners insurance, auto insurance, and other coverage options.</p>
                <div style="margin-top: var(--hph-space-6); padding-top: var(--hph-space-6); border-top: 1px solid var(--hph-gray-200);">
                    <p style="font-size: var(--hph-text-sm); color: var(--hph-gray-600); margin-bottom: var(--hph-space-2);"><strong>Contact:</strong></p>
                    <p style="font-size: var(--hph-text-sm); color: var(--hph-gray-600); margin: 0;">Devin Varca, Insurance Advisor<br>
                    302.943.5946<br>
                    dvarca@thecanopyinsurancegroup.com</p>
                </div>
            </div>',
            'link' => array(
                'text' => 'Learn More',
                'url' => 'https://thecanopyinsurancegroup.com'
            )
        ),
        array(
            'icon' => 'fas fa-home',
            'title' => 'Mustache Mortgage',
            'content' => '<div>
                <p style="margin-bottom: var(--hph-space-4);"><strong>Mortgage & Lending Services</strong></p>
                <p style="margin-bottom: var(--hph-space-4);">Mustache Mortgage offers a variety of loan programs including conventional, FHA, VA, USDA, and specialized first-time buyer programs to meet your financing needs.</p>
                <div style="margin-top: var(--hph-space-6); padding-top: var(--hph-space-6); border-top: 1px solid var(--hph-gray-200);">
                    <p style="font-size: var(--hph-text-sm); color: var(--hph-gray-600); margin-bottom: var(--hph-space-2);"><strong>Contact:</strong></p>
                    <p style="font-size: var(--hph-text-sm); color: var(--hph-gray-600); margin: 0;">Remy Haynes, Loan Officer<br>
                    NMLS 2433656<br>
                    302.604.3395<br>
                    rhaynes@mustachemortgages.com</p>
                </div>
            </div>',
            'link' => array(
                'text' => 'Learn More',
                'url' => 'https://mustachemortgages.com'
            )
        )
    )
);

get_template_part('template-parts/sections/content', null, $affiliated_businesses);

// Important Notice Section
$notice_section = array(
    'layout' => 'centered',
    'background' => 'white',
    'padding' => '2xl',
    'content_width' => 'narrow',
    'content' => '<div style="background: var(--hph-warning-light); padding: var(--hph-space-8); border-radius: var(--hph-radius-lg); border: 2px solid var(--hph-warning);">
        <h3 style="color: var(--hph-gray-900); margin: 0 0 var(--hph-space-6) 0; display: flex; align-items: center; gap: var(--hph-gap-md);">
            <i class="fas fa-exclamation-triangle" style="color: var(--hph-warning);"></i>
            Important Notice
        </h3>
        <p style="margin-bottom: var(--hph-space-4); font-weight: var(--hph-font-semibold);">The Parker Group may receive a financial benefit from these affiliated business arrangements.</p>
        <p style="margin: 0;">You are NOT required to use any of these affiliated service providers as a condition of the purchase, sale, or refinance of the subject property. There are other providers available with similar services, and you are free to shop around to determine that you are receiving the best services and rates for your needs.</p>
    </div>'
);

get_template_part('template-parts/sections/content', null, $notice_section);

// Your Rights Section
$rights_section = array(
    'layout' => 'centered',
    'background' => 'light',
    'padding' => '3xl',
    'headline' => 'Your Rights as a Consumer',
    'content' => '<div style="max-width: var(--hph-container-sm); margin: 0 auto;">
        <div style="display: grid; gap: var(--hph-gap-lg); margin-bottom: var(--hph-margin-2xl);">
            <div style="display: flex; gap: var(--hph-gap-md); align-items: start;">
                <i class="fas fa-check-circle" style="color: var(--hph-success); font-size: var(--hph-text-xl); margin-top: 2px; flex-shrink: 0;"></i>
                <div>
                    <h4 style="margin: 0 0 var(--hph-space-2) 0; color: var(--hph-gray-900);">Freedom of Choice</h4>
                    <p style="margin: 0; color: var(--hph-gray-600);">You have the right to choose any service provider for your transaction. You are not required to use our affiliated businesses.</p>
                </div>
            </div>
            <div style="display: flex; gap: var(--hph-gap-md); align-items: start;">
                <i class="fas fa-check-circle" style="color: var(--hph-success); font-size: var(--hph-text-xl); margin-top: 2px; flex-shrink: 0;"></i>
                <div>
                    <h4 style="margin: 0 0 var(--hph-space-2) 0; color: var(--hph-gray-900);">Shop and Compare</h4>
                    <p style="margin: 0; color: var(--hph-gray-600);">We encourage you to shop around and compare services and rates from multiple providers to ensure you\'re getting the best value.</p>
                </div>
            </div>
            <div style="display: flex; gap: var(--hph-gap-md); align-items: start;">
                <i class="fas fa-check-circle" style="color: var(--hph-success); font-size: var(--hph-text-xl); margin-top: 2px; flex-shrink: 0;"></i>
                <div>
                    <h4 style="margin: 0 0 var(--hph-space-2) 0; color: var(--hph-gray-900);">No Pressure</h4>
                    <p style="margin: 0; color: var(--hph-gray-600);">Your decision to use or not use our affiliated service providers will not affect the terms, quality, or completion of your real estate transaction.</p>
                </div>
            </div>
            <div style="display: flex; gap: var(--hph-gap-md); align-items: start;">
                <i class="fas fa-check-circle" style="color: var(--hph-success); font-size: var(--hph-text-xl); margin-top: 2px; flex-shrink: 0;"></i>
                <div>
                    <h4 style="margin: 0 0 var(--hph-space-2) 0; color: var(--hph-gray-900);">Full Transparency</h4>
                    <p style="margin: 0; color: var(--hph-gray-600);">We are committed to full disclosure of all business relationships and any financial benefits we may receive.</p>
                </div>
            </div>
        </div>
    </div>'
);

get_template_part('template-parts/sections/content', null, $rights_section);

// Legal Disclaimer Section
$disclaimer_section = array(
    'layout' => 'centered',
    'background' => 'dark',
    'padding' => '2xl',
    'content_width' => 'normal',
    'headline' => 'Legal Disclaimer',
    'content' => '<div style="font-size: var(--hph-text-sm); line-height: var(--hph-leading-relaxed); opacity: 0.9;">
        <p style="margin-bottom: var(--hph-space-4);">Clients are not required to use or recommend Canopy Insurance Group or Mustache Mortgage as a condition for the purchase, sale, or refinance of the subject property. There are other insurance providers and mortgage lenders available with similar services, and you are free to shop around to determine that you are receiving the best services and rates for your needs.</p>
        
        <p style="margin-bottom: var(--hph-space-4);">The Parker Group Real Estate may receive a financial or other benefit from your use of these affiliated service providers. However, your decision to use or not use these services will in no way affect your real estate transaction with The Parker Group.</p>
        
        <p style="margin: 0;">This disclosure is provided in accordance with the Real Estate Settlement Procedures Act (RESPA) and is intended to inform you of the business relationships that may exist between The Parker Group and other service providers. If you have any questions about these relationships or your rights as a consumer, please contact us directly.</p>
    </div>'
);

get_template_part('template-parts/sections/content', null, $disclaimer_section);

// Contact Section
$contact_section = array(
    'layout' => 'centered',
    'background' => 'white',
    'padding' => '2xl',
    'headline' => 'Questions About These Disclosures?',
    'content' => '<p style="font-size: var(--hph-text-lg); margin-bottom: var(--hph-space-8);">We\'re happy to discuss our business relationships and answer any questions you may have.</p>',
    'buttons' => array(
        array(
            'text' => 'Contact Us',
            'url' => '/contact',
            'style' => 'primary',
            'size' => 'lg',
            'icon' => 'fas fa-envelope'
        ),
        array(
            'text' => 'Call: 302.217.6692',
            'url' => 'tel:302-217-6692',
            'style' => 'outline',
            'size' => 'lg',
            'icon' => 'fas fa-phone'
        )
    )
);

get_template_part('template-parts/sections/content', null, $contact_section);

get_footer();
?>