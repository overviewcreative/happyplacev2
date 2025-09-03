<?php
/**
 * Base Carousel Component
 * 
 * Pure UI carousel/slider component for image galleries and content carousels
 * No data dependencies, just presentation
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Base
 * @since 3.0.0
 */

$props = wp_parse_args(hph_get_arg(), array(
    // Content
    'slides' => array(), // Array of slide content with 'content', 'image', 'caption' keys
    
    // Appearance
    'variant' => 'default', // default, fade, slide, card
    'aspect_ratio' => 'landscape', // square, landscape, portrait, wide, auto
    'height' => 'auto', // auto, fixed, viewport
    'size' => 'md', // sm, md, lg, xl
    
    // Navigation
    'show_arrows' => true, // Previous/next arrows
    'show_dots' => true, // Dot indicators
    'show_counter' => false, // Slide counter (1 of 5)
    'show_thumbnails' => false, // Thumbnail navigation
    
    // Behavior
    'autoplay' => false, // Auto-advance slides
    'autoplay_speed' => 5000, // Milliseconds
    'pause_on_hover' => true, // Pause autoplay on hover
    'infinite' => true, // Loop back to start
    'slides_to_show' => 1, // Number of slides visible
    'slides_to_scroll' => 1, // Number of slides to advance
    'responsive' => true, // Responsive behavior
    
    // Advanced
    'keyboard_nav' => true, // Keyboard navigation
    'touch_swipe' => true, // Touch/swipe support
    'lazy_load' => false, // Lazy load images
    'zoom' => false, // Image zoom on click
    'lightbox' => false, // Open in lightbox
    
    // HTML
    'id' => '',
    'class' => '',
    'attributes' => array(),
    'data' => array()
));

// Validate slides
if (empty($props['slides']) || !is_array($props['slides'])) {
    return;
}

// Generate unique ID if not provided
if (empty($props['id'])) {
    $props['id'] = 'hph-carousel-' . uniqid();
}

// Build carousel classes
$carousel_classes = array(
    'hph-carousel',
    'hph-carousel--' . $props['variant'],
    'hph-carousel--' . $props['aspect_ratio'],
    'hph-carousel--' . $props['size']
);

// Add multi-slide class when showing multiple slides
if ($props['slides_to_show'] > 1) {
    $carousel_classes[] = 'hph-carousel-multi';
    if ($props['slides_to_show'] > 3) {
        $carousel_classes[] = 'hph-carousel-multi-' . $props['slides_to_show'];
    }
}

if ($props['autoplay']) {
    $carousel_classes[] = 'hph-carousel--autoplay';
}

if ($props['infinite']) {
    $carousel_classes[] = 'hph-carousel--infinite';
}

if ($props['touch_swipe']) {
    $carousel_classes[] = 'hph-carousel--swipe';
}

if ($props['lazy_load']) {
    $carousel_classes[] = 'hph-carousel--lazy';
}

if (!empty($props['class'])) {
    $carousel_classes[] = $props['class'];
}

// Prepare data attributes
$data_attrs = array_merge(array(
    'slides-to-show' => $props['slides_to_show'],
    'slides-to-scroll' => $props['slides_to_scroll'],
    'autoplay' => $props['autoplay'] ? 'true' : 'false',
    'autoplay-speed' => $props['autoplay_speed'],
    'infinite' => $props['infinite'] ? 'true' : 'false',
    'pause-on-hover' => $props['pause_on_hover'] ? 'true' : 'false',
    'keyboard-nav' => $props['keyboard_nav'] ? 'true' : 'false',
    'touch-swipe' => $props['touch_swipe'] ? 'true' : 'false'
), $props['data']);

// Build attributes string
$attributes = array();
foreach ($props['attributes'] as $key => $value) {
    $attributes[] = esc_attr($key) . '="' . esc_attr($value) . '"';
}
foreach ($data_attrs as $key => $value) {
    $attributes[] = 'data-' . esc_attr($key) . '="' . esc_attr($value) . '"';
}
$attributes_string = implode(' ', $attributes);

$total_slides = count($props['slides']);
?>

<div 
    id="<?php echo esc_attr($props['id']); ?>"
    class="<?php echo esc_attr(implode(' ', $carousel_classes)); ?>"
    <?php echo $attributes_string; ?>
    role="region"
    aria-label="<?php esc_attr_e('Image carousel', 'happy-place-theme'); ?>"
>
    
    <!-- Carousel Container -->
    <div class="hph-carousel-inner">
        
        <!-- Slides Track -->
        <div class="hph-carousel-track" role="list">
            <?php foreach ($props['slides'] as $index => $slide): ?>
            <div 
                class="hph-carousel-slide <?php echo $index === 0 ? 'hph-carousel-slide--active' : ''; ?>"
                role="listitem"
                aria-label="<?php echo esc_attr(sprintf(__('Slide %d of %d', 'happy-place-theme'), $index + 1, $total_slides)); ?>"
                data-slide="<?php echo esc_attr($index); ?>"
            >
                
                <?php if (!empty($slide['image'])): ?>
                <div class="hph-carousel-media">
                    <img
                        src="<?php echo $props['lazy_load'] && $index > 0 ? '' : esc_url($slide['image']['src'] ?? ''); ?>"
                        <?php if ($props['lazy_load'] && $index > 0): ?>
                        data-src="<?php echo esc_url($slide['image']['src'] ?? ''); ?>"
                        <?php endif; ?>
                        alt="<?php echo esc_attr($slide['image']['alt'] ?? ''); ?>"
                        class="hph-carousel-image <?php echo $props['lazy_load'] ? 'hph-lazy' : ''; ?>"
                        <?php if ($props['zoom'] || $props['lightbox']): ?>
                        role="button"
                        tabindex="0"
                        <?php endif; ?>
                    >
                </div>
                <?php endif; ?>
                
                <?php if (!empty($slide['content'])): ?>
                <div class="hph-carousel-content">
                    <?php echo wp_kses_post($slide['content']); ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($slide['caption'])): ?>
                <div class="hph-carousel-caption">
                    <?php echo wp_kses_post($slide['caption']); ?>
                </div>
                <?php endif; ?>
                
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Navigation Arrows -->
        <?php if ($props['show_arrows'] && $total_slides > 1): ?>
        <button 
            class="hph-carousel-control hph-carousel-control-prev"
            type="button"
            aria-label="<?php esc_attr_e('Previous slide', 'happy-place-theme'); ?>"
        >
            <span class="hph-carousel-control-icon" data-icon="arrow-left" aria-hidden="true"></span>
        </button>
        
        <button 
            class="hph-carousel-control hph-carousel-control-next"
            type="button"
            aria-label="<?php esc_attr_e('Next slide', 'happy-place-theme'); ?>"
        >
            <span class="hph-carousel-control-icon" data-icon="arrow-right" aria-hidden="true"></span>
        </button>
        <?php endif; ?>
        
    </div>
    
    <!-- Dot Indicators -->
    <?php if ($props['show_dots'] && $total_slides > 1): ?>
    <div class="hph-carousel-indicators" role="tablist">
        <?php foreach ($props['slides'] as $index => $slide): ?>
        <button
            class="hph-carousel-indicator <?php echo $index === 0 ? 'is-active' : ''; ?>"
            type="button"
            role="tab"
            aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
            aria-label="<?php echo esc_attr(sprintf(__('Go to slide %d', 'happy-place-theme'), $index + 1)); ?>"
            data-slide="<?php echo esc_attr($index); ?>"
        ></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Counter -->
    <?php if ($props['show_counter'] && $total_slides > 1): ?>
    <div class="hph-carousel__counter">
        <span class="hph-carousel__current">1</span>
        <span class="hph-carousel__separator"> / </span>
        <span class="hph-carousel__total"><?php echo esc_html($total_slides); ?></span>
    </div>
    <?php endif; ?>
    
    <!-- Thumbnails -->
    <?php if ($props['show_thumbnails'] && $total_slides > 1): ?>
    <div class="hph-carousel__thumbnails">
        <?php foreach ($props['slides'] as $index => $slide): 
            if (empty($slide['image']['src'])) continue;
        ?>
        <button
            class="hph-carousel__thumbnail <?php echo $index === 0 ? 'hph-carousel__thumbnail--active' : ''; ?>"
            type="button"
            data-slide="<?php echo esc_attr($index); ?>"
            aria-label="<?php echo esc_attr(sprintf(__('Go to slide %d', 'happy-place-theme'), $index + 1)); ?>"
        >
            <img
                src="<?php echo esc_url($slide['image']['thumbnail'] ?? $slide['image']['src']); ?>"
                alt="<?php echo esc_attr($slide['image']['alt'] ?? ''); ?>"
                class="hph-carousel__thumbnail-image"
            >
        </button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
</div>
