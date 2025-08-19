<?php
/**
 * Featured Properties Section Template Part
 * 
 * @package HappyPlaceTheme
 */
?>

<section class="hph-section hph-section--lg">
    <div class="hph-container">
        <div class="hph-text-center hph-mb-12">
            <span class="hph-text-primary hph-text-uppercase hph-font-semibold hph-tracking-wide">
                <?php esc_html_e('Featured Properties', 'happy-place-theme'); ?>
            </span>
            <h2 class="hph-heading-2xl hph-font-bold hph-mt-2 hph-mb-4">
                <?php esc_html_e('Our Best Properties', 'happy-place-theme'); ?>
            </h2>
        </div>

        <!-- Temporary static content until API is ready -->
        <div class="hph-grid hph-grid--3 hph-gap-8">
            <?php for ($i = 1; $i <= 3; $i++) : ?>
                <div class="hph-card hph-card--property">
                    <div class="hph-card__media">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder-property.jpg" 
                             alt="Property <?php echo $i; ?>" 
                             class="hph-image hph-image--cover">
                    </div>
                    <div class="hph-card__body hph-p-6">
                        <div class="hph-flex hph-items-center hph-justify-between hph-mb-4">
                            <span class="hph-badge hph-badge--primary">
                                For Sale
                            </span>
                            <span class="hph-text-xl hph-font-bold hph-text-primary">
                                $<?php echo number_format(500000 + ($i * 100000), 0); ?>
                            </span>
                        </div>
                        <h3 class="hph-card__title">
                            Modern <?php echo $i === 1 ? 'House' : ($i === 2 ? 'Apartment' : 'Villa'); ?>
                        </h3>
                        <p class="hph-text-body hph-mb-4">
                            Beautiful <?php echo $i === 1 ? '4' : ($i === 2 ? '3' : '5'); ?> bedroom property with modern amenities
                        </p>
                        <div class="hph-property-features hph-border-t hph-pt-4">
                            <span class="hph-feature">
                                <i class="fas fa-bed hph-mr-2"></i> <?php echo $i === 1 ? '4' : ($i === 2 ? '3' : '5'); ?> Beds
                            </span>
                            <span class="hph-feature">
                                <i class="fas fa-bath hph-mr-2"></i> <?php echo $i + 1; ?> Baths
                            </span>
                            <span class="hph-feature">
                                <i class="fas fa-ruler-combined hph-mr-2"></i> <?php echo number_format(2000 + ($i * 500)); ?> sqft
                            </span>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <div class="hph-text-center hph-mt-12">
            <a href="<?php echo esc_url(home_url('/listings')); ?>" 
               class="hph-btn hph-btn--primary hph-btn--lg">
                <?php esc_html_e('View All Properties', 'happy-place-theme'); ?>
                <i class="fas fa-arrow-right hph-ml-2"></i>
            </a>
        </div>
    </div>
</section>
