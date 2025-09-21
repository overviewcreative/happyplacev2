<?php
/**
 * Template Name: Buyers Page
 * 
 * Comprehensive buyers resource page mirroring Happy Place Handbook structure
 * 
 * @package HappyPlaceTheme
 */

get_header();

// Hero Section - Welcome with image background
$hero_config = array(
    'style' => 'image',
    'background_image' => '/wp-content/uploads/buyers-hero-family-home.jpg',
    'overlay' => 'dark',
    'overlay_opacity' => '50',
    'height' => 'lg',
    'alignment' => 'center',
    'badge' => 'Happy Place Handbook',
    'badge_icon' => 'fas fa-home',
    'headline' => 'Your Journey to Finding Your Happy Place',
    'subheadline' => "Nobody ever said buying a home was simple, but that doesn't mean it has to be hard.",
    'content' => "We're with you, every step of the way.",
    'buttons' => array(
        array(
            'text' => 'Download Our Buyer Guide',
            'url' => '/happy-place-handbook.pdf',
            'style' => 'white',
            'size' => 'xl',
            'icon' => 'fas fa-download'
        ),
        array(
            'text' => 'Get Started',
            'url' => '#pre-approval',
            'style' => 'outline-white',
            'size' => 'xl',
            'icon' => 'fas fa-arrow-down'
        )
    ),
    'scroll_indicator' => true,
    'is_top_of_page' => true,
    'parallax' => true
);

get_template_part('template-parts/sections/hero', null, $hero_config);

// Section 1: Pre-Approval or Proof of Funds
$preapproval_section = array(
    'layout' => 'left-image',
    'background' => 'white',
    'padding' => '3xl',
    'section_id' => 'pre-approval',
    'image' => array(
        'url' => '/wp-content/uploads/preapproval-meeting.jpg',
        'alt' => 'Meeting with loan officer'
    ),
    'badge' => 'Step 01',
    'headline' => 'Pre-Approval or Proof of Funds',
    'content' => '<p>Getting pre-approved by a lender is an essential step when you\'re starting your home search, and provides you with:</p>
    <ul style="list-style: none; padding: 0; margin: var(--hph-space-6) 0;">
        <li style="padding: var(--hph-space-4) 0; border-bottom: 1px solid var(--hph-gray-200);"><strong>Market Confidence:</strong> Sellers will only consider cash offers with proof of funds or offers from pre-approved buyers.</li>
        <li style="padding: var(--hph-space-4) 0; border-bottom: 1px solid var(--hph-gray-200);"><strong>Financial Clarity:</strong> Know exactly what you can afford before falling in love with properties beyond your budget.</li>
        <li style="padding: var(--hph-space-4) 0;"><strong>Smoother Process:</strong> Move more easily from offer to closing with your financing already in motion.</li>
    </ul>
    <p>Your dedicated agent can connect you with our network of trusted lending partners who offer transparent, straightforward financing solutions tailored to your specific needs.</p>
    <div style="background: var(--hph-primary-light); padding: var(--hph-space-6); border-radius: var(--hph-radius-lg); margin-top: var(--hph-space-8);">
        <h4 style="color: var(--hph-primary); margin: 0 0 var(--hph-space-2) 0;">Financing Tips</h4>
        <p style="margin: 0;">Today\'s mortgage landscape offers numerous paths to homeownership — from conventional and FHA loans to VA, USDA, and specialized first-time buyer programs. Your agent will ensure you\'re paired with a lender who understands your homeownership goals.</p>
    </div>',
    'buttons' => array(
        array(
            'text' => 'Connect with a Lender',
            'url' => '/preferred-lenders',
            'style' => 'primary',
            'size' => 'lg',
            'icon' => 'fas fa-calculator'
        )
    )
);

get_template_part('template-parts/sections/content', null, $preapproval_section);

// Dark divider section
$dark_divider = array(
    'style' => 'gradient',
    'theme' => 'dark',
    'height' => 'sm',
    'alignment' => 'center',
    'headline' => 'Time to Meet Your Team',
    'content' => 'Our personalized approach begins with understanding you.'
);

get_template_part('template-parts/sections/hero', null, $dark_divider);

// Section 2: Consultation + Buyer Agreement
$consultation_section = array(
    'layout' => 'right-image',
    'background' => 'light',
    'padding' => '3xl',
    'image' => array(
        'url' => '/wp-content/uploads/consultation-meeting.jpg',
        'alt' => 'Agent consultation with buyers'
    ),
    'badge' => 'Step 02',
    'headline' => 'Consultation + Buyer Agency Agreement',
    'content' => '<p>Our personalized home buying consultation is designed to help us genuinely understand your needs, preferences, and priorities.</p>
    <h4 style="color: var(--hph-primary); margin: var(--hph-space-8) 0 var(--hph-space-4) 0;">Defining Your Criteria</h4>
    <p>The most satisfying home search begins with clearly defined wishes and needs. We\'ll help you clarify your priorities across location, property features, lifestyle needs, and future plans. This thoughtful foundation allows us to focus on properties that truly match your vision, saving valuable time and preventing decision fatigue.</p>
    <ul style="list-style: none; padding: 0; margin: var(--hph-space-6) 0;">
        <li style="padding: var(--hph-space-2) 0;"><i class="fas fa-check" style="color: var(--hph-success); margin-right: var(--hph-space-2);"></i> Define your must-haves vs. nice-to-haves</li>
        <li style="padding: var(--hph-space-2) 0;"><i class="fas fa-check" style="color: var(--hph-success); margin-right: var(--hph-space-2);"></i> Consider neighborhoods that align with your lifestyle</li>
        <li style="padding: var(--hph-space-2) 0;"><i class="fas fa-check" style="color: var(--hph-success); margin-right: var(--hph-space-2);"></i> Discuss market conditions and timing</li>
        <li style="padding: var(--hph-space-2) 0;"><i class="fas fa-check" style="color: var(--hph-success); margin-right: var(--hph-space-2);"></i> Explore your vision for your happy place</li>
    </ul>
    <p>You\'ll gain access to our helpful home search platform — a user-friendly tool that delivers more relevant property matches than traditional search methods.</p>
    <p>We will also provide a Consumer Information Statement explaining our professional relationship, followed by a Buyer Agency Agreement that outlines everyone\'s roles and responsibilities.</p>'
);

get_template_part('template-parts/sections/content', null, $consultation_section);

// Section 3: Defining Search + Drive-Bys
$search_section = array(
    'layout' => 'centered',
    'background' => 'white',
    'padding' => '3xl',
    'badge' => 'Step 03',
    'headline' => 'Defining Your Search + Drive-Bys',
    'content' => '<p style="font-size: var(--hph-text-lg); margin-bottom: var(--hph-space-8);">Based on the insights from your home buying consultation, we will have a clear picture of where to look.</p>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--hph-gap-xl); margin: var(--hph-margin-2xl) 0;">
        <div style="padding: var(--hph-space-8); background: var(--hph-gray-50); border-radius: var(--hph-radius-lg);">
            <h4 style="color: var(--hph-primary); margin: 0 0 var(--hph-space-4) 0;">Our Approach</h4>
            <p>We\'ll identify promising neighborhoods and properties based on your criteria, market conditions, and community insights that only local experts can provide.</p>
            <p>We recommend beginning with "drive-bys" — a preliminary look at potential homes to get a feel for neighborhoods and exteriors.</p>
        </div>
        <div style="padding: var(--hph-space-8); background: var(--hph-primary-light); border-radius: var(--hph-radius-lg);">
            <h4 style="color: var(--hph-primary); margin: 0 0 var(--hph-space-4) 0;">Drive-By Tips</h4>
            <p>Think about your lifestyle when planning drive-bys. Consider driving by during:</p>
            <ul style="list-style: none; padding: 0;">
                <li>• Morning and evening commute times</li>
                <li>• Weekend mornings</li>
                <li>• Weeknights</li>
                <li>• Middle of the day</li>
            </ul>
        </div>
    </div>
    <div style="background: var(--hph-warning-light); padding: var(--hph-space-6); border-radius: var(--hph-radius-lg); border-left: 4px solid var(--hph-warning);">
        <p style="margin: 0;"><strong>Important:</strong> If you discover a promising property during your explorations, contact your Parker Group agent directly rather than the listing agent shown on signs. Listing agents represent the seller\'s interests — your dedicated buyer\'s agent works exclusively for your best outcome.</p>
    </div>'
);

get_template_part('template-parts/sections/content', null, $search_section);

// Image break - neighborhood aerial
$neighborhood_hero = array(
    'style' => 'image',
    'background_image' => '/wp-content/uploads/neighborhood-aerial.jpg',
    'overlay' => 'primary-gradient',
    'height' => 'sm',
    'alignment' => 'center',
    'headline' => 'From Exploration to Discovery',
    'content' => 'Every home search is unique, but our experience guides you to the right properties faster.',
    'parallax' => true
);

get_template_part('template-parts/sections/hero', null, $neighborhood_hero);

// Section 4: Private Tours
$tours_section = array(
    'layout' => 'left-image',
    'background' => 'light',
    'padding' => '3xl',
    'image' => array(
        'url' => '/wp-content/uploads/home-tour.jpg',
        'alt' => 'Agent showing home to buyers'
    ),
    'badge' => 'Step 04',
    'headline' => 'Narrowing Your Search + Private Tours',
    'content' => '<p>Once we\'ve narrowed your list to homes that best fit your vision, we\'ll schedule private tours to explore each home thoroughly.</p>
    <p>During these private tours, your agent will accompany you and provide detailed information for you to consider. These private tours with your Agent are the final step in deciding which home you want to make an offer on, and what that offer should be.</p>
    <div style="margin: var(--hph-margin-2xl) 0;">
        <h4 style="color: var(--hph-primary); margin-bottom: var(--hph-space-6);">What We\'ll Review Together:</h4>
        <div style="display: grid; gap: var(--hph-gap-lg);">
            <div style="padding: var(--hph-space-4); border-left: 3px solid var(--hph-primary);">
                <h5 style="margin: 0 0 var(--hph-space-2) 0;">Complete Property Information</h5>
                <p style="margin: 0; color: var(--hph-gray-600);">Everything you need to know about the home including utilities, age, noteworthy features, and HOA fees.</p>
            </div>
            <div style="padding: var(--hph-space-4); border-left: 3px solid var(--hph-primary);">
                <h5 style="margin: 0 0 var(--hph-space-2) 0;">Seller\'s Disclosure Documents</h5>
                <p style="margin: 0; color: var(--hph-gray-600);">Review what sellers know about the property condition, past repairs, and system functionality.</p>
            </div>
            <div style="padding: var(--hph-space-4); border-left: 3px solid var(--hph-primary);">
                <h5 style="margin: 0 0 var(--hph-space-2) 0;">Market Insights</h5>
                <p style="margin: 0; color: var(--hph-gray-600);">Recent price adjustments, comparable listings, neighborhood data, and days on market.</p>
            </div>
        </div>
    </div>'
);

get_template_part('template-parts/sections/content', null, $tours_section);

// Section 5: Making an Offer
$offer_section = array(
    'layout' => 'right-image',
    'background' => 'white',
    'padding' => '3xl',
    'image' => array(
        'url' => '/wp-content/uploads/making-offer.jpg',
        'alt' => 'Signing offer documents'
    ),
    'badge' => 'Step 05',
    'headline' => 'Making an Offer + Negotiations',
    'content' => '<p style="font-size: var(--hph-text-lg); margin-bottom: var(--hph-space-8);">When you\'ve found a home that feels right, we\'ll help you develop a thoughtful offer designed to secure the property while protecting your interests.</p>
    <p>Our approach combines market knowledge, understanding of seller perspectives, and property value assessment to craft a strong offer that works for you.</p>
    <h4 style="color: var(--hph-primary); margin: var(--hph-space-8) 0 var(--hph-space-6) 0;">Your Agent Will Guide You Through:</h4>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--hph-gap-md);">
        <div style="padding: var(--hph-space-6); background: var(--hph-gray-50); border-radius: var(--hph-radius-md);">
            <i class="fas fa-tag" style="color: var(--hph-primary); font-size: var(--hph-text-xl); margin-bottom: var(--hph-space-2); display: block;"></i>
            <strong>Appropriate Offer Price</strong>
        </div>
        <div style="padding: var(--hph-space-6); background: var(--hph-gray-50); border-radius: var(--hph-radius-md);">
            <i class="fas fa-money-check-alt" style="color: var(--hph-primary); font-size: var(--hph-text-xl); margin-bottom: var(--hph-space-2); display: block;"></i>
            <strong>Suitable Deposit Amount</strong>
        </div>
        <div style="padding: var(--hph-space-6); background: var(--hph-gray-50); border-radius: var(--hph-radius-md);">
            <i class="fas fa-calendar" style="color: var(--hph-primary); font-size: var(--hph-text-xl); margin-bottom: var(--hph-space-2); display: block;"></i>
            <strong>Timeline Considerations</strong>
        </div>
        <div style="padding: var(--hph-space-6); background: var(--hph-gray-50); border-radius: var(--hph-radius-md);">
            <i class="fas fa-shield-alt" style="color: var(--hph-primary); font-size: var(--hph-text-xl); margin-bottom: var(--hph-space-2); display: block;"></i>
            <strong>Contingency Options</strong>
        </div>
        <div style="padding: var(--hph-space-6); background: var(--hph-gray-50); border-radius: var(--hph-radius-md);">
            <i class="fas fa-search" style="color: var(--hph-primary); font-size: var(--hph-text-xl); margin-bottom: var(--hph-space-2); display: block;"></i>
            <strong>Inspection Provisions</strong>
        </div>
        <div style="padding: var(--hph-space-6); background: var(--hph-gray-50); border-radius: var(--hph-radius-md);">
            <i class="fas fa-file-contract" style="color: var(--hph-primary); font-size: var(--hph-text-xl); margin-bottom: var(--hph-space-2); display: block;"></i>
            <strong>Financing Terms</strong>
        </div>
    </div>
    <div style="background: var(--hph-primary-light); padding: var(--hph-space-6); border-radius: var(--hph-radius-lg); margin-top: var(--hph-space-8);">
        <h4 style="color: var(--hph-primary); margin: 0 0 var(--hph-space-2) 0;">Negotiations</h4>
        <p style="margin: 0;">After an offer is submitted, the seller may accept, reject, or counter. The negotiation phase requires both thoughtful consideration and timely action. Your Parker Group agent will use their experience to advocate for your priorities while keeping the process moving forward smoothly.</p>
    </div>'
);

get_template_part('template-parts/sections/content', null, $offer_section);

// Continue with remaining sections following the same pattern...
// Section 6: Ratified Contract
// Section 7: Attorney + Title Company
// Section 8: Inspections
// Section 9: Preparing for Your Happy Place
// Section 10: Settlement Day

// Final CTA
$final_cta = array(
    'layout' => 'split',
    'background' => 'gradient',
    'padding' => '3xl',
    'badge' => 'Ready to Begin?',
    'headline' => 'Let\'s Find Your Happy Place',
    'subheadline' => 'Schedule your free consultation today',
    'content' => 'Thank you for trusting us with this important chapter in your story. We can\'t wait to help you discover where your happy place will be.',
    'form' => array(
        'title' => 'Quick Contact',
        'button_text' => 'Start My Journey'
    ),
    'animation' => true
);

get_template_part('template-parts/sections/cta', null, $final_cta);

get_footer();
?>