<?php
/**
 * Template Name: Dashboard Under Construction
 * Description: Under construction page for the agent dashboard
 */

// Don't show admin bar for clean look
show_admin_bar(false);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?><?php bloginfo('name'); ?> - Agent Dashboard</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('agent-dashboard'); ?>>

<main id="main-content" class="site-main">
    
    <?php
    // ============================================
    // Dashboard Construction Hero Section
    // ============================================
    get_template_part('template-parts/sections/hero', null, array(
        'style' => 'gradient',
        'theme' => 'primary',
        'height' => 'lg',
        'is_top_of_page' => true,
        'background_image' => '',
        'parallax' => false,
        'overlay' => 'gradient',
        'alignment' => 'center',
        'headline' => 'Agent Dashboard Coming Soon',
        'subheadline' => "We're building something pretty cool for you. In the meantime, access your essential tools and forms below.",
        'content_width' => 'normal',
        'fade_in' => true,
        'scroll_indicator' => false,
        'section_id' => 'dashboard-hero',
        'buttons' => array(
            array(
                'text' => 'Agent Huddle',
                'url' => 'http://meet.google.com/raq-afya-anv',
                'style' => 'white',
                'size' => 'm',
                'target' => '_blank'
            ),
            'button' => array(
                    'text' => 'Open Cheatsheet',
                    'url' => 'https://docs.google.com/document/d/1XVswBY6Wsx9W6JckpVqmWbvxh6kyaeaFLZNbOyQUNb4/edit?usp=drive_link',
                    'style' => 'primary',
                    'size' => 'm',
                    'target' => '_blank'
            ),
        ),
    ));
    
    
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
        'icon_position' => 'top',
        'animation' => true,
        'section_id' => 'dashboard-tools',
        'features' => array(
            array(
                'title' => 'Agent Cheatsheet',
                'content' => 'Helpful links and resources for Parker Group',
                'button' => array(
                    'text' => 'Open Cheatsheet',
                    'url' => 'https://docs.google.com/document/d/1XVswBY6Wsx9W6JckpVqmWbvxh6kyaeaFLZNbOyQUNb4/edit?usp=drive_link',
                    'style' => 'primary',
                    'size' => 'md',
                    'target' => '_blank'
                )
            ),
            // Lead Generation Forms
            array(
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

<?php wp_footer(); ?>
</body>
</html>
