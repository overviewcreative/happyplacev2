<?php
/**
 * Template Name: Under Construction Page
 *
 * Elegant under construction page for sections being developed
 *
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Don't show admin bar for clean look
show_admin_bar(false);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?><?php bloginfo('name'); ?></title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Preload hero image for faster loading -->
    <link rel="preload" as="image" href="<?php echo get_template_directory_uri(); ?>/assets/images/hero-bg3.jpg">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            /* Happy Place Brand Colors */
            --hph-primary: #3b82f6;
            --hph-primary-light: #60a5fa;
            --hph-primary-dark: #1e40af;
            --hph-secondary: #10b981;
            --hph-white: #ffffff;
            --hph-gray-100: #f3f4f6;
            --hph-gray-900: #111827;

            /* Spacing tokens with numerical values */
            --spacing-1: 0.25rem;
            --spacing-2: 0.5rem;
            --spacing-3: 0.75rem;
            --spacing-4: 1rem;
            --spacing-5: 1.25rem;
            --spacing-6: 1.5rem;
            --spacing-8: 2rem;
            --spacing-10: 2.5rem;
            --spacing-12: 3rem;
            --spacing-16: 4rem;
            --spacing-20: 5rem;
            --spacing-24: 6rem;

            /* Typography */
            --font-display: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --font-body: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        body, html {
            height: 100%;
            width: 100%;
            font-family: var(--font-body);
            overflow: hidden;
        }

        .hero-container {
            position: relative;
            width: 100%;
            height: 100vh;
            background-image: url('<?php echo function_exists('hph_get_image_url') ? hph_get_image_url('hero-bg3.jpg') : get_template_directory_uri() . '/assets/images/hero-bg3.jpg'; ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;

            /* Ken Burns effect */
            animation: kenBurns 20s ease-in-out infinite alternate;
        }

        @keyframes kenBurns {
            0% {
                transform: scale(1);
            }
            100% {
                transform: scale(1.1);
            }
        }

        .hero-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.8), rgba(16, 185, 129, 0.6));
            z-index: 1;
        }

        .content-wrapper {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            padding: var(--spacing-8);
            max-width: 900px;
            animation: fadeInUp 1.2s ease-out;
        }

        .logo-section {
            margin-bottom: var(--spacing-8);
            animation: fadeIn 1s ease-out 0.3s both;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-section img {
            max-width: 250px;
            height: auto;
            filter: brightness(0) invert(1);
            opacity: 0.95;
            display: block;
            margin: 0 auto;
        }

        .logo-section .brand-text {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 300;
            letter-spacing: 0.1em;
            margin: 0;
            text-transform: uppercase;
        }

        h1 {
            font-family: var(--font-display);
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 700;
            margin-bottom: var(--spacing-6);
            letter-spacing: -0.02em;
            line-height: 1.1;
            background: linear-gradient(135deg, var(--hph-white), rgba(255, 255, 255, 0.8));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            font-size: clamp(1.1rem, 2.2vw, 1.6rem);
            font-weight: 300;
            margin-bottom: var(--spacing-10);
            opacity: 0.95;
            line-height: 1.6;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .feature-list {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: var(--spacing-6);
            margin: var(--spacing-8) 0;
            animation: fadeIn 1s ease-out 0.8s both;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .feature-icon {
            width: 16px;
            height: 16px;
            background: var(--hph-secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: white;
        }

        .divider {
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
            margin: var(--spacing-8) auto;
            border-radius: 2px;
        }

        .cta-section {
            margin-top: var(--spacing-10);
            animation: fadeIn 1s ease-out 1s both;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-2);
            padding: var(--spacing-4) var(--spacing-8);
            background: rgba(255, 255, 255, 0.15);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            font-size: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            margin: 0 var(--spacing-2);
        }

        .btn-primary:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }

        .progress-indicator {
            display: inline-flex;
            gap: var(--spacing-3);
            margin-top: var(--spacing-8);
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            animation: pulse 1.8s infinite ease-in-out;
        }

        .dot:nth-child(1) { animation-delay: 0s; }
        .dot:nth-child(2) { animation-delay: 0.3s; }
        .dot:nth-child(3) { animation-delay: 0.6s; }

        .eta-info {
            margin-top: var(--spacing-6);
            padding: var(--spacing-4) var(--spacing-6);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: inline-block;
        }

        .eta-label {
            font-size: 0.875rem;
            opacity: 0.8;
            margin-bottom: var(--spacing-1);
        }

        .eta-date {
            font-weight: 600;
            font-size: 1.1rem;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes pulse {
            0%, 100% {
                background: rgba(255, 255, 255, 0.4);
                transform: scale(1);
            }
            50% {
                background: rgba(255, 255, 255, 0.9);
                transform: scale(1.4);
            }
        }

        /* Floating particles effect */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content-wrapper {
                padding: var(--spacing-6);
            }

            h1 {
                margin-bottom: var(--spacing-4);
            }

            .subtitle {
                margin-bottom: var(--spacing-6);
            }

            .feature-list {
                flex-direction: column;
                gap: var(--spacing-3);
            }

            .btn-primary {
                display: block;
                margin: var(--spacing-2) 0;
            }
        }

        @media (max-width: 480px) {
            .logo-section img {
                max-width: 150px;
            }

            .eta-info {
                margin-top: var(--spacing-4);
                padding: var(--spacing-3) var(--spacing-4);
            }
        }

        /* Disable Ken Burns on reduced motion */
        @media (prefers-reduced-motion: reduce) {
            .hero-container {
                animation: none;
            }

            .content-wrapper,
            .logo-section,
            .feature-list,
            .cta-section {
                animation: none;
            }
        }
    </style>

    <?php wp_head(); ?>
</head>
<body <?php body_class('under-construction'); ?>>

    <div class="hero-container">
        <!-- Floating particles effect -->
        <div class="particles" id="particles"></div>

        <div class="content-wrapper">
            <!-- Logo Section -->
            <div class="logo-section">
                <?php
                $site_logo = get_theme_mod('custom_logo');
                if ($site_logo) {
                    $logo_url = wp_get_attachment_image_url($site_logo, 'full');
                    echo '<img src="' . esc_url($logo_url) . '" alt="' . get_bloginfo('name') . '">';
                } else {
                    $tpg_logo_light = get_template_directory_uri() . '/assets/logos/TPG Logo Light.png';
                    echo '<img src="' . esc_url($tpg_logo_light) . '" alt="The Parker Group" style="max-width: 250px; height: auto;">';
                }
                ?>
            </div>

            <h1><?php echo get_the_title() ?: 'Coming Soon'; ?></h1>

            <p class="subtitle">
                <?php
                $content = get_the_content();
                if ($content) {
                    echo wp_strip_all_tags($content);
                } else {
                    echo "This page is currently under construction.";
                }
                ?>
            </p>

            <div class="divider"></div>

            <!-- CTA Buttons -->
            <div class="cta-section">
                <a href="https://search.parkergroupsells.com/search/map?s[orderBy]=featured&s[page]=1&s[bbox]=38.830380586773515%2C-75.05765152684316%2C38.51409016575655%2C-75.69485855809316" target="_blank" rel="noopener noreferrer" class="btn-primary">
                    <i class="fas fa-search"></i>
                    <span>Browse Properties</span>
                </a>

                <button type="button" class="btn-primary modal-trigger"
                       data-modal-form="general-contact"
                       data-modal-title="Contact Us"
                       data-modal-subtitle="Send us a message and we'll get back to you soon.">
                    <i class="fas fa-envelope"></i>
                    <span>Contact Us</span>
                </button>

                <a href="tel:+13024567200" class="btn-primary">
                    <i class="fas fa-phone"></i>
                    <span>Call Us</span>
                </a>
            </div>


            <div class="progress-indicator">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </div>

    <script>
    // Create floating particles effect
    function createParticles() {
        const particlesContainer = document.getElementById('particles');
        const particleCount = 15;

        for (let i = 0; i < particleCount; i++) {
            setTimeout(() => {
                const particle = document.createElement('div');
                particle.className = 'particle';

                // Random size and position
                const size = Math.random() * 4 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDuration = (Math.random() * 3 + 4) + 's';
                particle.style.animationDelay = Math.random() * 2 + 's';

                particlesContainer.appendChild(particle);

                // Remove particle after animation
                setTimeout(() => {
                    if (particle.parentNode) {
                        particle.parentNode.removeChild(particle);
                    }
                }, 8000);
            }, i * 300);
        }
    }

    // Start particles effect
    createParticles();

    // Repeat particles every 8 seconds
    setInterval(createParticles, 8000);

    // Preload next page for smooth transition
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.btn-primary');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = this.href;
                document.head.appendChild(link);
            });
        });
    });
    </script>

    <?php wp_footer(); ?>
</body>
</html>