<?php
/**
 * Footer Template
 * 
 * The footer for the Happy Place theme
 *
 * @package HappyPlaceTheme
 */

// Get contact info
$agency_phone = get_option('hph_agency_phone', '(302) 555-0123');
$agency_email = get_option('hph_agency_email', 'info@happyplace.com');
$agency_hours = get_option('hph_agency_hours', 'Mon-Fri 9AM-6PM');
?>

    <!-- Footer Section -->
    <footer id="colophon" class="hph-footer" role="contentinfo">
        <div class="hph-footer-main">
            <div class="hph-container">
                
                <!-- Footer Content Grid -->
                <div class="hph-footer-grid">
                    
                    <!-- Company Information -->
                    <div class="hph-footer-section hph-footer-about">
                        <h3 class="hph-footer-title">
                            <?php bloginfo('name'); ?>
                        </h3>
                        <p class="hph-footer-description">
                            <?php 
                            $description = get_bloginfo('description');
                            if ($description) {
                                echo esc_html($description);
                            } else {
                                esc_html_e('Your trusted real estate partner for finding the perfect home.', 'happy-place-theme');
                            }
                            ?>
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
                            <li><a href="<?php echo esc_url(home_url('/mortgages/')); ?>"><?php esc_html_e('Mortgages', 'happy-place-theme'); ?></a></li>
                            <li><a href="<?php echo esc_url(home_url('/listings/')); ?>"><?php esc_html_e('Property Search', 'happy-place-theme'); ?></a></li>
                            <li><a href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Market Analysis', 'happy-place-theme'); ?></a></li>
                        </ul>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="hph-footer-section hph-footer-contact-section">
                        <h3 class="hph-footer-title">
                            <?php esc_html_e('Get in Touch', 'happy-place-theme'); ?>
                        </h3>
                        <div class="hph-footer-contact-info">
                            <p><?php echo esc_html($agency_hours); ?></p>
                            <p><strong>Licensed Real Estate Professionals</strong></p>
                            <p>Serving Delaware & Surrounding Areas</p>
                        </div>
                    </div>
                    
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="hph-footer-bottom">
                <div class="hph-container">
                    <div class="hph-footer-bottom-content">
                        <div class="hph-footer-copyright">
                            <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('All rights reserved.', 'happy-place-theme'); ?></p>
                        </div>
                        <div class="hph-footer-legal">
                            <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy Policy</a>
                            <a href="<?php echo esc_url(home_url('/terms/')); ?>">Terms of Service</a>
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

wp_footer(); 
?>

</body>
</html>
