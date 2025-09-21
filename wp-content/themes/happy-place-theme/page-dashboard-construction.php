<?php
/**
 * Template Name: Dashboard Under Construction
 * Description: Password-protected under construction page for the agent dashboard
 */

// Password protection
if (!is_user_logged_in() && !is_admin()) {
    // Show password form if not logged in
    if (!isset($_POST['dashboard_password'])) {
        get_header(); ?>
        
        <main id="main-content" class="site-main">
            <div class="hph-container" style="padding: 4rem 1.5rem; text-align: center; min-height: 60vh; display: flex; flex-direction: column; justify-content: center;">
                <div style="max-width: 400px; margin: 0 auto;">
                    <i class="fas fa-lock" style="font-size: 3rem; color: var(--hph-primary); margin-bottom: 2rem;"></i>
                    <h1 style="margin-bottom: 1rem;">Dashboard Access</h1>
                    <p style="color: var(--hph-gray-600); margin-bottom: 2rem;">This page is password protected. Please enter the password to continue.</p>
                    
                    <form method="post" style="display: flex; flex-direction: column; gap: 1rem;">
                        <input 
                            type="password" 
                            name="dashboard_password" 
                            placeholder="Enter password"
                            style="padding: 0.75rem; border: 1px solid var(--hph-gray-300); border-radius: 0.5rem; font-size: 1rem;"
                            required
                        >
                        <button 
                            type="submit" 
                            style="padding: 0.75rem; background: var(--hph-primary); color: white; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer;"
                        >
                            Access Dashboard
                        </button>
                    </form>
                </div>
            </div>
        </main>
        
        <?php get_footer();
        exit;
    } else {
        // Check password (you can change this to any password you want)
        $correct_password = 'dashboard2024'; // Change this password
        if ($_POST['dashboard_password'] !== $correct_password) {
            // Redirect back with error
            wp_redirect(add_query_arg('error', '1', get_permalink()));
            exit;
        }
        // Password is correct, continue to show the page
    }
}

get_header(); ?>

<main id="main-content" class="site-main">
    
    <?php
    // ============================================
    // Dashboard Construction Hero Section
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'gradient',
        'theme' => 'primary',
        'height' => 'lg',
        'background_image' => '',
        'parallax' => false,
        'overlay' => 'gradient',
        'alignment' => 'center',
        'headline' => 'Agent Dashboard Coming Soon',
        'subheadline' => 'Your comprehensive real estate management platform is under development',
        'content' => 'We\'re building something amazing for you. In the meantime, access your essential tools and forms below.',
        'content_width' => 'normal',
        'fade_in' => true,
        'scroll_indicator' => false,
        'section_id' => 'dashboard-hero'
    ));
    
    // ============================================
    // Construction Status Section
    // ============================================
    ?>
    <section style="padding: 4rem 0; background: var(--hph-gray-50);">
        <div class="hph-container" style="max-width: var(--hph-container-xl); margin: 0 auto; padding: 0 1.5rem; text-align: center;">
            <div style="max-width: 800px; margin: 0 auto;">
                <div style="margin-bottom: 3rem;">
                    <span style="display: inline-block; padding: 0.5rem 1rem; background: var(--hph-primary-100); color: var(--hph-primary-700); border-radius: 2rem; font-size: 0.875rem; font-weight: 600; margin-bottom: 1.5rem;">
                        🚧 Under Construction
                    </span>
                    <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--hph-gray-900);">
                        Building Your Digital Headquarters
                    </h2>
                    <p style="font-size: 1.125rem; color: var(--hph-gray-600); line-height: 1.6;">
                        We're crafting a powerful dashboard that will streamline your workflow, manage your listings, track your leads, and provide insightful analytics. While we put the finishing touches on your new command center, you can access your essential tools below.
                    </p>
                </div>
                
                <!-- Progress Indicators -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-top: 3rem;">
                    <div style="text-align: center;">
                        <div style="width: 60px; height: 60px; background: var(--hph-success); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <i class="fas fa-check" style="color: white; font-size: 1.5rem;"></i>
                        </div>
                        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">Planning Complete</h3>
                        <p style="color: var(--hph-gray-600); font-size: 0.875rem;">Architecture & design finalized</p>
                    </div>
                    
                    <div style="text-align: center;">
                        <div style="width: 60px; height: 60px; background: var(--hph-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <i class="fas fa-code" style="color: white; font-size: 1.5rem;"></i>
                        </div>
                        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">Development Active</h3>
                        <p style="color: var(--hph-gray-600); font-size: 0.875rem;">Core features being built</p>
                    </div>
                    
                    <div style="text-align: center;">
                        <div style="width: 60px; height: 60px; background: var(--hph-gray-300); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <i class="fas fa-rocket" style="color: white; font-size: 1.5rem;"></i>
                        </div>
                        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">Launch Soon</h3>
                        <p style="color: var(--hph-gray-600); font-size: 0.875rem;">Testing & final preparations</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php
    // ============================================
    // Available Tools & Forms Section
    // ============================================
    get_template_part('template-parts/sections/features', null, array(
        'layout' => 'grid',
        'background' => 'white',
        'padding' => '2xl',
        'content_width' => 'wide',
        'alignment' => 'center',
        'columns' => 3,
        'badge' => 'Available Now',
        'headline' => 'Essential Agent Tools',
        'subheadline' => 'Access your most important forms and management tools',
        'content' => 'While we finish building your comprehensive dashboard, these essential tools remain available to keep your business running smoothly.',
        'icon_style' => 'gradient',
        'icon_position' => 'top',
        'animation' => true,
        'section_id' => 'dashboard-tools',
        'features' => array(
            // Lead Generation Forms
            array(
                'icon' => 'fas fa-camera',
                'title' => 'Schedule Listing Photography & Content',
                'content' => 'Request professional photos, virtual tour, and floorplans for your property listings.',
                'button' => array(
                    'text' => 'Open Form',
                    'url' => 'https://wb0g0aooeuf.typeform.com/to/QbMIkBor',
                    'style' => 'primary',
                    'size' => 'md',
                    'target' => '_blank'
                )
            ),
            array(
                'icon' => 'fas fa-star',
                'title' => 'Listing Spotlight Request',
                'content' => 'Request a featured listing spot on our homepage and marketing channels.',
                'button' => array(
                    'text' => 'Open Form',
                    'url' => 'https://docs.google.com/forms/d/e/1FAIpQLSc-14_2D05bMJu_lgbl0jGS79yF-y_FZL3ziwnxaqX32fbe8A/viewform',
                    'style' => 'primary',
                    'size' => 'md',
                    'target' => '_blank'
                )
            ),
            array(
                'icon' => 'fas fa-smile',
                'title' => 'Happy Place of The Week Request',
                'content' => 'Request to have your listing featured as our Happy Place of the Week on WRDE.',
                'button' => array(
                    'text' => 'Open Form',
                    'url' => 'https://docs.google.com/forms/d/e/1FAIpQLSf9wKm2EY_-KEUGDoc1ZwoVlQNiQXGj_chJ4GKOu1K8GSH6Kg/viewform?pli=1',
                    'style' => 'primary',
                    'size' => 'md',
                    'target' => '_blank'
                )
            ),
            
            // Client Management
            array(
                'icon' => 'fas fa-brain',
                'title' => 'Continuing Education + Licensure Submission',
                'content' => 'Submit your CE credits and license renewals for tracking and reimbursement.',
                'button' => array(
                    'text' => 'Open Form',
                    'url' => 'https://docs.google.com/forms/d/e/1FAIpQLSdq9ygwsAA1JEytaz4iedpOtfgyww5SiJfYfXweBg6BBIkg2Q/viewform?pli=1',
                    'style' => 'primary',
                    'size' => 'md',
                    'target' => '_blank'
                )
            ),
            array(
                'icon' => 'fas fa-calendar-check',
                'title' => 'One on One with Julie',
                'content' => 'Book your personal coaching session with Julie to strategize your business growth.',
                'button' => array(
                    'text' => 'Open Form',
                    'url' => 'https://calendar.google.com/calendar/u/0/appointments/schedules/AcZssZ1NzwNqMEfFzhYSaE3p3s66rIY0jZN2tzYQdjciedz8OszgaJFcA7QsXZbhctlwZImZgP0MmvNp',
                    'style' => 'primary',
                    'size' => 'md',
                    'target' => '_blank'
                )
            ),
            array(
                'icon' => 'fas fa-star',
                'title' => 'Open House Marketing Request',
                'content' => 'Request flyers, signs and promotion for an upcoming open houses.',
                'button' => array(
                    'text' => 'Open Form',
                    'url' => 'https://wb0g0aooeuf.typeform.com/to/ddcgvd6r?typeform-source=www.google.com',
                    'style' => 'primary',
                    'size' => 'md',
                    'target' => '_blank'
                )
            ),
            
            // Property & Marketing
            array(
                'icon' => 'fas fa-lock',
                'title' => 'Lockbox & Signage Request',
                'content' => 'Request lockboxes, yard signs, and directional signage for your listings.',
                'button' => array(
                    'text' => 'Open Form',
                    'url' => 'https://wb0g0aooeuf.typeform.com/to/C43J6hku',
                    'style' => 'primary',
                    'size' => 'md',
                    'target' => '_blank'
                )
            ),
            array(
                'icon' => 'fas fa-clipboard-list',
                'title' => 'EMD and Mutual Release Requests',
                'content' => 'Submit Earnest Money Deposit requests for your transactions.',
                'button' => array(
                    'text' => 'Open Form',
                    'url' => 'https://docs.google.com/forms/d/e/1FAIpQLSe9dNO7GWnrOvqLmapAFONueNpRZLPrZFt40Ni8Ej9epv3y6w/viewform?pli=1',
                    'style' => 'primary',
                    'size' => 'md',
                    'target' => '_blank'
                )
            ),
            array(
                'icon' => 'fas fa-handshake',
                'title' => 'New Buyer Contact Submission',
                'content' => 'Submit buyer contract for trasaction coordination and processing.',
                'button' => array(
                    'text' => 'Open Form',
                    'url' => 'https://wb0g0aooeuf.typeform.com/to/lPW5dxrZ?typeform-source=www.google.com',
                    'style' => 'primary',
                    'size' => 'md',
                    'target' => '_blank'
                )
            ),
            
            // Administrative & Reports
            array(
                'icon' => 'fas fa-dollar-sign',
                'title' => 'Pay your Annual E&O',
                'content' => 'Submit your Errors & Omissions insurance payment. Due annually by November 1.',
                'button' => array(
                    'text' => 'Open Form',
                    'url' => 'https://wb0g0aooeuf.typeform.com/to/xJN5DMr9?typeform-source=www.google.com',
                    'style' => 'primary',
                    'size' => 'md',
                    'target' => '_blank'
                )
            ),
            array(
                'icon' => 'fas fa-cogs',
                'title' => 'RealtyFlow CRM',
                'content' => 'Access your comprehensive customer relationship management system.',
                'button' => array(
                    'text' => 'Sign In',
                    'url' => 'https://app.realtyflow.co/signin',
                    'style' => 'primary',
                    'size' => 'md',
                    'target' => '_blank'
                )
            ),
        )
    ));
    
    // ============================================
    // Coming Soon Features Section
    // ============================================
    get_template_part('template-parts/sections/features', null, array(
        'layout' => 'grid',
        'background' => 'light',
        'padding' => 'xl',
        'content_width' => 'normal',
        'alignment' => 'center',
        'columns' => 3,
        'badge' => 'Coming Soon',
        'headline' => 'Dashboard Features in Development',
        'subheadline' => 'What to expect in your new agent dashboard',
        'content' => 'These powerful features are being crafted to transform how you manage your real estate business.',
        'icon_style' => 'outline',
        'icon_position' => 'top',
        'animation' => true,
        'features' => array(
            array(
                'icon' => 'fas fa-chart-bar',
                'title' => 'Performance Analytics',
                'content' => 'Comprehensive dashboards showing your sales metrics, lead conversion rates, and market performance.'
            ),
            array(
                'icon' => 'fas fa-tasks',
                'title' => 'Lead Management',
                'content' => 'Centralized lead tracking with automated follow-ups, scoring, and conversion pipeline management.'
            ),
            array(
                'icon' => 'fas fa-building',
                'title' => 'Listing Management',
                'content' => 'Complete listing lifecycle management from initial input to final sale with automated updates.'
            ),
            array(
                'icon' => 'fas fa-calendar-alt',
                'title' => 'Schedule Management',
                'content' => 'Integrated calendar system for showings, appointments, and important deadlines.'
            ),
            array(
                'icon' => 'fas fa-users',
                'title' => 'Client Portal',
                'content' => 'Dedicated client access areas with document sharing and communication tools.'
            ),
            array(
                'icon' => 'fas fa-mobile-alt',
                'title' => 'Mobile Optimization',
                'content' => 'Full mobile responsiveness for managing your business on the go from any device.'
            )
        )
    ));
    ?>
    
</main>

<?php get_footer(); ?>
