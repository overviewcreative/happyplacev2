<?php
/**
 * Services Section Template Part
 * 
 * @package HappyPlaceTheme
 */
?>

<section class="py-16 md:py-24">
    <div class="hph-container">
        <div class="text-center mb-12">
            <span class="text-secondary font-semibold uppercase tracking-wider">
                <?php esc_html_e('Our Services', 'happy-place-theme'); ?>
            </span>
            <h2 class="text-3xl md:text-4xl font-bold mt-2 mb-4">
                <?php esc_html_e('Complete Real Estate Solutions', 'happy-place-theme'); ?>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Buying Service -->
            <div class="bg-white rounded-lg p-8 shadow-sm hover:shadow-md transition-shadow">
                <div class="text-primary text-4xl mb-4">
                    <i class="fas fa-home"></i>
                </div>
                <h3 class="text-xl font-bold mb-3"><?php esc_html_e('Buying', 'happy-place-theme'); ?></h3>
                <p class="text-gray-600 mb-4">
                    <?php esc_html_e('Find your dream property with our expert guidance and local market knowledge.', 'happy-place-theme'); ?>
                </p>
                <a href="<?php echo esc_url(home_url('/services/buying')); ?>" class="text-primary font-semibold hover:underline">
                    <?php esc_html_e('Learn More', 'happy-place-theme'); ?> &rarr;
                </a>
            </div>

            <!-- Selling Service -->
            <div class="bg-white rounded-lg p-8 shadow-sm hover:shadow-md transition-shadow">
                <div class="text-secondary text-4xl mb-4">
                    <i class="fas fa-key"></i>
                </div>
                <h3 class="text-xl font-bold mb-3"><?php esc_html_e('Selling', 'happy-place-theme'); ?></h3>
                <p class="text-gray-600 mb-4">
                    <?php esc_html_e('Maximize your property\'s value with our strategic marketing and negotiation expertise.', 'happy-place-theme'); ?>
                </p>
                <a href="<?php echo esc_url(home_url('/services/selling')); ?>" class="text-secondary font-semibold hover:underline">
                    <?php esc_html_e('Learn More', 'happy-place-theme'); ?> &rarr;
                </a>
            </div>

            <!-- Investment Service -->
            <div class="bg-white rounded-lg p-8 shadow-sm hover:shadow-md transition-shadow">
                <div class="text-tertiary text-4xl mb-4">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="text-xl font-bold mb-3"><?php esc_html_e('Investment', 'happy-place-theme'); ?></h3>
                <p class="text-gray-600 mb-4">
                    <?php esc_html_e('Grow your wealth through strategic real estate investments and portfolio management.', 'happy-place-theme'); ?>
                </p>
                <a href="<?php echo esc_url(home_url('/services/investment')); ?>" class="text-tertiary font-semibold hover:underline">
                    <?php esc_html_e('Learn More', 'happy-place-theme'); ?> &rarr;
                </a>
            </div>
        </div>
    </div>
</section>
