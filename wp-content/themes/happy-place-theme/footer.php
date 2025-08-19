<?php
/**
 * Footer Template
 * 
 * The footer for the Happy Place theme
 *
 * @package HappyPlaceTheme
 */
?>

    <!-- Footer Section -->
    <footer id="colophon" class="site-footer section-dark" role="contentinfo">
        <div class="footer-main section section-lg" 
             style="background: var(--hph-gray-900); color: var(--hph-white); padding: var(--hph-section-padding) 0;">
            <div class="section-container">
                
                <!-- Footer Content Grid -->
                <div class="footer-grid content-grid content-grid-4" 
                     style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--hph-space-2xl);">
                    
                    <!-- Company Information -->
                    <div class="footer-section footer-about">
                        <h3 class="footer-title" 
                            style="font-size: var(--hph-text-lg); font-weight: 600; margin-bottom: var(--hph-space-lg); color: var(--hph-white);">
                            <?php bloginfo('name'); ?>
                        </h3>
                        <p class="footer-description" 
                           style="font-size: var(--hph-text-sm); line-height: var(--hph-leading-relaxed); margin-bottom: var(--hph-space-lg); color: var(--hph-gray-300);">
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
                        <?php if (function_exists('hpt_get_company_info')) : ?>
                            <?php 
                            $company_info = hpt_get_company_info();
                            ?>
                            <div class="footer-contact-info">
                                <?php if (!empty($company_info['phone'])) : ?>
                                    <p class="footer-contact-item" 
                                       style="display: flex; align-items: center; margin-bottom: var(--hph-space-sm); color: var(--hph-gray-300);">
                                        <i class="fas fa-phone" style="margin-right: var(--hph-space-sm); width: 1rem;"></i>
                                        <a href="tel:<?php echo esc_attr($company_info['phone']); ?>" 
                                           style="color: var(--hph-gray-300); text-decoration: none; transition: var(--hph-transition-fast);"
                                           onmouseover="this.style.color='var(--hph-white)'"
                                           onmouseout="this.style.color='var(--hph-gray-300)'">
                                            <?php echo esc_html($company_info['phone']); ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($company_info['email'])) : ?>
                                    <p class="footer-contact-item" 
                                       style="display: flex; align-items: center; margin-bottom: var(--hph-space-sm); color: var(--hph-gray-300);">
                                        <i class="fas fa-envelope" style="margin-right: var(--hph-space-sm); width: 1rem;"></i>
                                        <a href="mailto:<?php echo esc_attr($company_info['email']); ?>" 
                                           style="color: var(--hph-gray-300); text-decoration: none; transition: var(--hph-transition-fast);"
                                           onmouseover="this.style.color='var(--hph-white)'"
                                           onmouseout="this.style.color='var(--hph-gray-300)'">
                                            <?php echo esc_html($company_info['email']); ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="footer-section footer-links">
                        <h3 class="footer-title" 
                            style="font-size: var(--hph-text-lg); font-weight: 600; margin-bottom: var(--hph-space-lg); color: var(--hph-white);">
                            <?php esc_html_e('Quick Links', 'happy-place-theme'); ?>
                        </h3>
                        <ul class="footer-nav" 
                            style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: var(--hph-space-sm);">
                            <li>
                                <a href="<?php echo esc_url(home_url('/')); ?>" 
                                   style="font-size: var(--hph-text-sm); color: var(--hph-gray-300); text-decoration: none; transition: var(--hph-transition-fast);"
                                   onmouseover="this.style.color='var(--hph-white)'"
                                   onmouseout="this.style.color='var(--hph-gray-300)'">
                                   <?php esc_html_e('Home', 'happy-place-theme'); ?>
                                </a>
                            </li>
                            
                            <?php if (post_type_exists('listing')) : ?>
                                <li>
                                    <a href="<?php echo esc_url(get_post_type_archive_link('listing')); ?>" 
                                       style="font-size: var(--hph-text-sm); color: var(--hph-gray-300); text-decoration: none; transition: var(--hph-transition-fast);"
                                       onmouseover="this.style.color='var(--hph-white)'"
                                       onmouseout="this.style.color='var(--hph-gray-300)'">
                                       <?php esc_html_e('Properties', 'happy-place-theme'); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (post_type_exists('agent')) : ?>
                                <li>
                                    <a href="<?php echo esc_url(get_post_type_archive_link('agent')); ?>" 
                                       style="font-size: var(--hph-text-sm); color: var(--hph-gray-300); text-decoration: none; transition: var(--hph-transition-fast);"
                                       onmouseover="this.style.color='var(--hph-white)'"
                                       onmouseout="this.style.color='var(--hph-gray-300)'">
                                       <?php esc_html_e('Our Agents', 'happy-place-theme'); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <li>
                                <a href="<?php echo esc_url(home_url('/about')); ?>" 
                                   style="font-size: var(--hph-text-sm); color: var(--hph-gray-300); text-decoration: none; transition: var(--hph-transition-fast);"
                                   onmouseover="this.style.color='var(--hph-white)'"
                                   onmouseout="this.style.color='var(--hph-gray-300)'">
                                   <?php esc_html_e('About Us', 'happy-place-theme'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url(home_url('/contact')); ?>" 
                                   style="font-size: var(--hph-text-sm); color: var(--hph-gray-300); text-decoration: none; transition: var(--hph-transition-fast);"
                                   onmouseover="this.style.color='var(--hph-white)'"
                                   onmouseout="this.style.color='var(--hph-gray-300)'">
                                   <?php esc_html_e('Contact', 'happy-place-theme'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Services -->
                    <div class="footer-section">
                        <h3 class="footer-title text-lg font-semibold mb-4 text-white"><?php esc_html_e('Services', 'happy-place-theme'); ?></h3>
                        <ul class="footer-links space-y-2">
                            <li><a href="#" class="text-sm text-gray-300 hover:text-white transition-colors"><?php esc_html_e('Buy a Home', 'happy-place-theme'); ?></a></li>
                            <li><a href="#" class="text-sm text-gray-300 hover:text-white transition-colors"><?php esc_html_e('Sell a Home', 'happy-place-theme'); ?></a></li>
                            <li><a href="#" class="text-sm text-gray-300 hover:text-white transition-colors"><?php esc_html_e('Property Valuation', 'happy-place-theme'); ?></a></li>
                            <li><a href="#" class="text-sm text-gray-300 hover:text-white transition-colors"><?php esc_html_e('Market Analysis', 'happy-place-theme'); ?></a></li>
                            
                            <?php if (post_type_exists('open-house')) : ?>
                                <li><a href="<?php echo esc_url(get_post_type_archive_link('open-house')); ?>" class="text-sm text-gray-300 hover:text-white transition-colors"><?php esc_html_e('Open Houses', 'happy-place-theme'); ?></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <!-- Recent Listings -->
                    <div class="footer-section">
                        <h3 class="footer-title text-lg font-semibold mb-4 text-white"><?php esc_html_e('Recent Listings', 'happy-place-theme'); ?></h3>
                        
                        <?php if (function_exists('hpt_get_recent_listings')) : ?>
                            <?php 
                            $recent_listings = hpt_get_recent_listings(3);
                            if ($recent_listings) :
                            ?>
                                <div class="widget-listings space-y-3">
                                    <?php foreach ($recent_listings as $listing) : ?>
                                        <?php 
                                        $listing_data = hpt_get_listing($listing->ID);
                                        $thumbnail = hpt_get_listing_featured_image($listing->ID);
                                        ?>
                                        <div class="listing-mini-card flex gap-3 p-2 hover-lift-subtle">
                                            <?php if ($thumbnail) : ?>
                                                <div class="listing-mini-image">
                                                    <img src="<?php echo esc_url($thumbnail['sizes']['thumbnail'] ?? $thumbnail['url']); ?>" 
                                                         alt="<?php echo esc_attr($listing->post_title); ?>" 
                                                         class="img-cover rounded w-12 h-12">
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="listing-mini-info flex-grow">
                                                <h4 class="text-sm font-medium mb-1">
                                                    <a href="<?php echo esc_url(get_permalink($listing->ID)); ?>" 
                                                       class="text-gray-300 hover:text-white transition-colors">
                                                        <?php echo esc_html($listing->post_title); ?>
                                                    </a>
                                                </h4>
                                                
                                                <?php if (!empty($listing_data['price'])) : ?>
                                                    <div class="price text-xs text-accent font-semibold">
                                                        <?php echo esc_html(hpt_get_listing_price_formatted($listing->ID)); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
            
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom" 
                 style="border-top: 1px solid var(--hph-gray-600); padding-top: var(--hph-space-xl); margin-top: var(--hph-space-2xl);">
                <div style="display: flex; flex-direction: column; gap: var(--hph-space-lg); align-items: center; @media (min-width: 768px) { flex-direction: row; justify-content: space-between; }">
                    <div class="footer-copyright">
                        <p style="font-size: var(--hph-text-sm); color: var(--hph-gray-400); margin: 0;">
                            &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('All rights reserved.', 'happy-place-theme'); ?>
                        </p>
                    </div>
                    
                    <div class="footer-menu-wrapper">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'footer',
                            'menu_class'     => 'footer-bottom-menu',
                            'container'      => false,
                            'depth'          => 1,
                            'fallback_cb'    => false,
                            'items_wrap'     => '<ul id="%1$s" class="%2$s" style="display: flex; gap: var(--hph-space-lg); list-style: none; margin: 0; padding: 0;">%3$s</ul>',
                            'link_before'    => '<span style="font-size: var(--hph-text-sm); color: var(--hph-gray-400); text-decoration: none; transition: var(--hph-transition-fast);" onmouseover="this.style.color=\'var(--hph-white)\'" onmouseout="this.style.color=\'var(--hph-gray-400)\'">',
                            'link_after'     => '</span>',
                        ));
                        ?>
                    </div>
                </div>
            </div>
            
        </div>
    </footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
