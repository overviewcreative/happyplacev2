<?php
/**
 * Hero Section Template Part
 * 
 * @package HappyPlaceTheme
 */

// Get hero content from customizer
$hero_image = get_theme_mod('hp_hero_image', get_template_directory_uri() . '/assets/images/hero-bg.jpg');
$hero_title = get_theme_mod('hp_hero_title', __('Discover Your Perfect Property', 'happy-place-theme'));
$hero_subtitle = get_theme_mod('hp_hero_subtitle', __('Find your dream home with our curated selection of premium properties.', 'happy-place-theme'));
?>

<section class="hero hero-full section-gradient">
    <div class="hero-bg" style="background-image: url('<?php 
        // Try to get image from theme first, then fall back to plugin
        $fallback_image = get_template_directory_uri() . '/assets/images/hero-bg.jpg';
        echo esc_url($hero_image ? $hero_image : $fallback_image); 
    ?>');"></div>
    <div class="section-overlay"></div>
    
    <div class="section-container section-container-narrow">
        <div class="hero-content text-center">
            <div class="status-badge status-badge-accent mb-4">
                <?php esc_html_e('Premium Real Estate Platform', 'happy-place-theme'); ?>
            </div>
            
            <h1 class="hero-title display-1 mb-4">
                <?php echo esc_html($hero_title); ?>
            </h1>
            
            <p class="hero-subtitle lead mb-8">
                <?php echo esc_html($hero_subtitle); ?>
            </p>
            
            <div class="search-panel bg-white shadow-lg">
                <form action="<?php echo esc_url(home_url('/')); ?>" method="get" class="search-form">
                    <div class="form-group">
                        <input type="text" name="s" 
                               placeholder="<?php esc_attr_e('Search by location or property type...', 'happy-place-theme'); ?>"
                               class="form-control form-control-lg">
                        <input type="hidden" name="post_type" value="listing">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <?php esc_html_e('Search', 'happy-place-theme'); ?>
                    </button>
                </form>
            </div>
        </div>
        </div>
    </div>
</section>
