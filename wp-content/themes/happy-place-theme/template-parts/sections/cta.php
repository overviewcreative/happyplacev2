<?php
/**
 * CTA Section Template Part
 * 
 * @package HappyPlaceTheme
 */
?>

<section class="py-16 md:py-24 bg-gradient-hero-primary relative">
    <div class="absolute inset-0 bg-pattern opacity-10"></div>
    <div class="hph-container relative z-1">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-high-contrast-light text-3xl md:text-4xl font-bold mb-6">
                <?php esc_html_e('Ready to Find Your Dream Home?', 'happy-place-theme'); ?>
            </h2>
            <p class="text-high-contrast-light text-xl mb-8">
                <?php esc_html_e('Connect with our expert team to start your property journey today.', 'happy-place-theme'); ?>
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="<?php echo esc_url(home_url('/contact')); ?>" class="hph-button">
                    <?php esc_html_e('Contact Us', 'happy-place-theme'); ?>
                </a>
                <a href="<?php echo esc_url(get_post_type_archive_link('listing')); ?>" class="hph-button-secondary">
                    <?php esc_html_e('Browse Properties', 'happy-place-theme'); ?>
                </a>
            </div>
        </div>
    </div>
</section>
