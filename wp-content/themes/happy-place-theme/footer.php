<?php
/**
 * Footer Template
 *
 * The footer for the Happy Place theme
 *
 * @package HappyPlaceTheme
 */

// Get contact info
$agency_phone = get_option('hph_agency_phone', '(302) 217-6692');
$agency_email = get_option('hph_agency_email', 'cheers@theparkergroup.com');
$agency_hours = get_option('hph_agency_hours', 'Mon-Fri 9AM-6PM');

// Get social links (same as header)
$social_links = array(
    'facebook' => get_option('hph_facebook_url', '#'),
    'instagram' => get_option('hph_instagram_url', '#'),
    'linkedin' => get_option('hph_linkedin_url', '#')
);

// Get the legal page URL (update the slug if different)
$legal_page_url = home_url('/legal');
?>

    <!-- Footer Section -->
    <footer id="colophon" class="hph-footer" role="contentinfo">
        <div class="hph-footer-main">
            <div class="hph-container">
                
                <!-- Footer Content Grid -->
                <div class="hph-footer-grid">
                    
                    <!-- Company Information -->
                    <div class="hph-footer-section hph-footer-about">
                        <!-- Company Logo -->
                        <div class="hph-footer-logo">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/logos/TPG Logo Simplified.png"
                                 alt="The Parker Group"
                                 class="hph-footer-logo-img"
                                 width="180"
                                 height="auto"
                                 loading="lazy">
                        </div>

                        <p class="hph-footer-tagline">
                            <?php esc_html_e('Find your happy place.', 'happy-place-theme'); ?>
                        </p>

                        <!-- Company Contact Information -->
                        <div class="hph-footer-contact">
                            <p class="hph-footer-contact-item">
                                <i class="fas fa-phone"></i>
                                <a href="tel:<?php echo esc_attr($agency_phone); ?>">
                                    <?php echo esc_html($agency_phone); ?>
                                </a>
                            </p>
                            <p class="hph-footer-contact-item">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:<?php echo esc_attr($agency_email); ?>">
                                    <?php echo esc_html($agency_email); ?>
                                </a>
                            </p>
                        </div>

                        <!-- Social Links -->
                        <div class="hph-social-links hph-social-links--footer">
                            <?php foreach ($social_links as $platform => $url) : ?>
                            <a href="<?php echo esc_url($url); ?>"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="hph-social-link"
                               aria-label="<?php echo esc_attr(ucfirst($platform)); ?>">
                                <i class="fab fa-<?php echo esc_attr($platform); ?>"></i>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="hph-footer-section hph-footer-links">
                        <h3 class="hph-footer-title">
                            <?php esc_html_e('Quick Links', 'happy-place-theme'); ?>
                        </h3>
                        <ul class="hph-footer-nav">
                            <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'happy-place-theme'); ?></a></li>
                            <li><a href="<?php echo esc_url(home_url('/listings/')); ?>"><?php esc_html_e('Listings', 'happy-place-theme'); ?></a></li>
                            <li><a href="<?php echo esc_url(home_url('/buyers/')); ?>"><?php esc_html_e('Buyers', 'happy-place-theme'); ?></a></li>
                            <li><a href="<?php echo esc_url(home_url('/sellers/')); ?>"><?php esc_html_e('Sellers', 'happy-place-theme'); ?></a></li>
                            <li><a href="<?php echo esc_url(home_url('/about/')); ?>"><?php esc_html_e('About Us', 'happy-place-theme'); ?></a></li>
                            <li><a href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Contact', 'happy-place-theme'); ?></a></li>
                        </ul>
                    </div>
                    
                    <!-- Services -->
                    <div class="hph-footer-section hph-footer-services">
                        <h3 class="hph-footer-title">
                            <?php esc_html_e('Services', 'happy-place-theme'); ?>
                        </h3>
                        <ul class="hph-footer-nav">
                            <li><a href="<?php echo esc_url(home_url('/buyers/')); ?>"><?php esc_html_e('Buy a Home', 'happy-place-theme'); ?></a></li>
                            <li><a href="<?php echo esc_url(home_url('/sellers/')); ?>"><?php esc_html_e('Sell a Home', 'happy-place-theme'); ?></a></li>
                            <?php /* Temporarily commented out as requested */ ?>
                            <?php /* <li><a href="<?php echo esc_url(home_url('/mortgages/')); ?>"><?php esc_html_e('Mortgages', 'happy-place-theme'); ?></a></li> */ ?>
                            <li><a href="<?php echo esc_url(home_url('/listings/')); ?>"><?php esc_html_e('Property Search', 'happy-place-theme'); ?></a></li>
                            <?php /* <li><a href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Market Analysis', 'happy-place-theme'); ?></a></li> */ ?>
                        </ul>
                    </div>
                    
                </div>
                
                <!-- Additional Compliance Information -->
                <div class="hph-footer-compliance">
                    <div class="hph-footer-compliance-badges">
                        <!-- Equal Housing Opportunity -->
                        <div class="hph-compliance-item">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/equal-housing-logo.svg"
                                 alt="Equal Housing Opportunity Logo"
                                 width="28"
                                 height="28"
                                 loading="lazy">
                            <span><?php esc_html_e('Equal Housing Opportunity', 'happy-place-theme'); ?></span>
                        </div>

                        <!-- Realtor Logo -->
                        <div class="hph-compliance-item">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/realtor-logo.svg"
                                 alt="REALTOR® Logo"
                                 width="42"
                                 height="24"
                                 loading="lazy">
                            <span><?php esc_html_e('REALTOR®', 'happy-place-theme'); ?></span>
                        </div>

                        <!-- State Licenses -->
                        <div class="hph-compliance-item">
                            <span><?php esc_html_e('Licensed in DE & MD', 'happy-place-theme'); ?></span>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Footer Bottom -->
            <div class="hph-footer-bottom">
                <div class="hph-container">
                    <div class="hph-footer-bottom-content">
                        <div class="hph-footer-copyright">
                            <p>&copy; <?php echo date('Y'); ?> The Parker Group. <?php esc_html_e('All rights reserved.', 'happy-place-theme'); ?></p>
                        </div>
                        <div class="hph-footer-legal">
                            <!-- Quick legal links for prominence -->
                            <a href="<?php echo esc_url($legal_page_url . '#privacy'); ?>"><?php esc_html_e('Privacy', 'happy-place-theme'); ?></a>
                            <span class="separator">|</span>
                            <a href="<?php echo esc_url($legal_page_url . '#terms'); ?>"><?php esc_html_e('Terms', 'happy-place-theme'); ?></a>
                            <span class="separator">|</span>
                            <a href="<?php echo esc_url($legal_page_url . '#fair-housing'); ?>"><?php esc_html_e('Fair Housing', 'happy-place-theme'); ?></a>
                            <span class="separator">|</span>
                            <a href="<?php echo esc_url($legal_page_url . '#accessibility'); ?>"><?php esc_html_e('Accessibility', 'happy-place-theme'); ?></a>
                            <span class="separator">|</span>
                            <a href="<?php echo esc_url($legal_page_url . '#dmca'); ?>"><?php esc_html_e('DMCA', 'happy-place-theme'); ?></a>
                            <span class="separator">|</span>
                            <a href="<?php echo esc_url($legal_page_url . '#cookies'); ?>"><?php esc_html_e('Cookies', 'happy-place-theme'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </footer><!-- #colophon -->

            </div><!-- .hph-content-container -->
        </div><!-- .hph-main-wrapper -->
    </div><!-- #main -->

</div><!-- #page -->

<?php
// Include global form modal
get_template_part('template-parts/components/custom-form-modal');

// Include Fello search widget modal
get_template_part('template-parts/components/custom-form-modal', null, [
    'modal_id' => 'hph-fello-modal',
    'form_template' => 'fello-search-widget',
    'modal_title' => __('Search Homes', 'happy-place-theme'),
    'modal_subtitle' => __('Find your perfect home with our advanced search tools.', 'happy-place-theme'),
    'modal_size' => 'large',
    'close_on_success' => false
]);

// Include consultation modal (for schedule consultation buttons)
get_template_part('template-parts/components/custom-form-modal', null, [
    'modal_id' => 'hph-consultation-modal',
    'form_template' => 'general-contact',
    'modal_title' => __('Schedule Consultation', 'happy-place-theme'),
    'modal_subtitle' => __('Let\'s discuss your real estate goals and how we can help.', 'happy-place-theme'),
    'modal_size' => 'medium',
    'form_args' => [
        'title' => __('Schedule Your Consultation', 'happy-place-theme'),
        'description' => __('Tell us about your real estate needs and we\'ll schedule a consultation.', 'happy-place-theme'),
        'submit_text' => __('Schedule Consultation', 'happy-place-theme'),
        'show_message_type' => true,
        'default_message_type' => 'consultation'
    ]
]);

wp_footer();
?>

<!-- EMERGENCY MODAL FIX - DIRECT INJECTION -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚨 EMERGENCY MODAL FIX LOADING...');

    // Force override any existing modal functions
    window.openHphFormModal = function(modalId = 'hph-form-modal') {
        console.log('🚨 EMERGENCY: Opening modal:', modalId);
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            console.log('✅ EMERGENCY: Modal opened successfully');
        } else {
            console.log('❌ EMERGENCY: Modal not found:', modalId);
        }
    };

    window.closeHphFormModal = function(modalId = null) {
        if (modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        } else {
            const activeModal = document.querySelector('.hph-modal.active');
            if (activeModal) {
                activeModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
    };

    // Only inject minimal CSS if framework styles aren't loading
    const existingModalCSS = document.querySelector('style[data-emergency-modal]');
    if (existingModalCSS) existingModalCSS.remove();

    // Test if framework modal CSS is working
    const testModal = document.querySelector('.hph-modal');
    if (testModal) {
        testModal.classList.add('active');
        const computedStyle = getComputedStyle(testModal);

        if (computedStyle.display !== 'flex') {
            console.log('🚨 EMERGENCY: Framework CSS not loaded, injecting minimal fallback...');
            const modalCSS = document.createElement('style');
            modalCSS.setAttribute('data-emergency-modal', 'true');
            modalCSS.textContent = `
            .hph-modal.active {
                display: flex !important;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100% !important;
                background: rgba(0, 0, 0, 0.5) !important;
                z-index: 10000 !important;
                align-items: center !important;
                justify-content: center !important;
                animation: modalFadeIn 0.3s ease !important;
            }

            /* Fix modal form layout issues */
            .hph-modal .hph-checkbox-group {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 0.75rem !important;
            }

            .hph-modal .hph-form-text {
                margin-top: 0.5rem !important;
                margin-bottom: 0 !important;
                clear: both !important;
                display: block !important;
            }

            .hph-modal .hph-form-textarea {
                margin-bottom: 0.5rem !important;
            }
            `;
            document.head.appendChild(modalCSS);
        } else {
            console.log('✅ EMERGENCY: Framework CSS is working correctly');
        }
        testModal.classList.remove('active');
    }

    // Force event handlers with aggressive targeting
    document.addEventListener('click', function(e) {
        // Target ALL possible modal buttons
        const heroBtn = e.target.closest('.hph-hero-btn[data-modal-id]');
        const modalTrigger = e.target.closest('.modal-trigger');
        const dataModalId = e.target.closest('[data-modal-id]');
        const dataModalForm = e.target.closest('[data-modal-form]');
        const trigger = heroBtn || modalTrigger || dataModalId || dataModalForm;

        if (trigger && (trigger.dataset.modalId || trigger.classList.contains('modal-trigger'))) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🚨 EMERGENCY: Modal trigger clicked:', trigger.textContent.trim().substring(0, 30));

            const modalId = trigger.dataset.modalId || 'hph-form-modal';
            window.openHphFormModal(modalId);
            return false;
        }

        // Close handlers
        if (e.target.matches('[data-modal-close]') ||
            e.target.closest('[data-modal-close]') ||
            e.target.classList.contains('hph-modal-backdrop')) {
            window.closeHphFormModal();
        }
    }, true); // Use capture phase

    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.closeHphFormModal();
        }
    });

    console.log('🚨 EMERGENCY MODAL FIX COMPLETE - ALL BUTTONS SHOULD WORK NOW');
});
</script>

</body>
</html>