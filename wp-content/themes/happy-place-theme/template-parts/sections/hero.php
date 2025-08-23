<?php
/**
 * HPH Hero Section Template
 * 
 * A specialized hero section template for fullwidth, impactful headers:
 * - Fullwidth background images with overlay options
 * - Gradient backgrounds and combinations
 * - Multiple height variations (sm, md, lg, xl, full)
 * - Advanced typography and CTA positioning
 * - Video background support
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - style: 'minimal' | 'gradient' | 'image' | 'video' | 'split' | 'property'
 * - height: 'sm' | 'md' | 'lg' | 'xl' | 'full'
 * - background_image: string (URL)
 * - background_video: string (URL for MP4)
 * - overlay: 'none' | 'light' | 'dark' | 'gradient' | 'gradient-reverse' | 'gradient-radial' | 'primary' | 'primary-gradient'
 * - overlay_opacity: string ('20', '40', '60', '80')
 * - alignment: 'left' | 'center' | 'right'
 * - content_width: 'narrow' | 'normal' | 'wide' | 'full'
 * - badge: string (optional)
 * - badge_icon: string (optional icon class)
 * - headline: string
 * - subheadline: string (optional)
 * - content: string
 * - buttons: array of button configurations
 * - scroll_indicator: boolean
 * - section_id: string (optional)
 * - parallax: boolean (optional) - adds parallax effect to background image
 * - fade_in: boolean (optional) - adds fade in animation to content
 * 
 * Property-specific args:
 * - listing_id: int (for property hero)
 * - show_gallery: boolean
 * - show_status: boolean
 * - show_price: boolean
 * - show_stats: boolean
 */

// Default arguments
$defaults = array(
    'style' => 'gradient',
    'height' => 'lg',
    'background_image' => '',
    'background_video' => '',
    'overlay' => 'dark',
    'overlay_opacity' => '40',
    'alignment' => 'center',
    'content_width' => 'normal',
    'badge' => '',
    'badge_icon' => '',
    'headline' => 'Hero Section',
    'subheadline' => '',
    'content' => '',
    'buttons' => array(),
    'scroll_indicator' => false,
    'section_id' => '',
    'parallax' => false,
    'fade_in' => false,
    // Property-specific defaults
    'listing_id' => 0,
    'show_gallery' => false,
    'show_status' => false,
    'show_price' => false,
    'show_stats' => false
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);

// Extract configuration
$style = $config['style'];
$height = $config['height'];
$background_image = $config['background_image'];
$background_video = $config['background_video'];
$overlay = $config['overlay'];
$overlay_opacity = $config['overlay_opacity'];
$alignment = $config['alignment'];
$content_width = $config['content_width'];
$badge = $config['badge'];
$badge_icon = $config['badge_icon'];
$headline = $config['headline'];
$subheadline = $config['subheadline'];
$content = $config['content'];
$buttons = $config['buttons'];
$scroll_indicator = $config['scroll_indicator'];
$section_id = $config['section_id'];
$parallax = $config['parallax'];
$fade_in = $config['fade_in'];
// Property-specific configuration
$listing_id = $config['listing_id'];
$show_gallery = $config['show_gallery'];
$show_status = $config['show_status'];
$show_price = $config['show_price'];
$show_stats = $config['show_stats'];

// AUTO-FIX: Switch to image style if background_image is provided but style is default gradient
if (!empty($background_image) && $style === 'gradient') {
    $style = 'image';
}

// Property-specific auto-configuration
if ($style === 'property' && $listing_id) {
    // Get property data using bridge functions
    if (function_exists('hpt_get_listing_title') && empty($headline)) {
        $headline = hpt_get_listing_title($listing_id);
    }
    if (function_exists('hpt_get_listing_address') && empty($subheadline)) {
        $subheadline = hpt_get_listing_address($listing_id);
    }
    if (function_exists('hpt_get_listing_featured_image') && empty($background_image)) {
        $background_image = hpt_get_listing_featured_image($listing_id);
    }
    
    // Auto-switch to image style if we have a background image
    if (!empty($background_image)) {
        $style = 'image';
    }
}

// Build hero classes
$hero_classes = array(
    'hph-hero',
    'hph-hero-' . $style,
    'hph-hero-' . $height,
    'hph-relative',
    'hph-flex',
    'hph-items-center',
    'hph-w-full',
    'hph-overflow-hidden'
);

// Add class if background image exists
if ($background_image) {
    $hero_classes[] = 'has-bg-image';
    if ($parallax) {
        $hero_classes[] = 'hph-hero-parallax';
    }
}

// Add overlay classes
if ($overlay !== 'none') {
    $hero_classes[] = 'hph-hero-overlay-' . $overlay;
    $hero_classes[] = 'hph-hero-opacity-' . $overlay_opacity;
}

// Build content container classes
$container_classes = array(
    'hph-hero-content',
    'hph-relative',
    'hph-z-10',
    'hph-w-full'
);

// Add fade in animation if enabled
if ($fade_in) {
    $container_classes[] = 'hph-fade-in';
}

// Add text color based on style
if ($style === 'minimal') {
    $container_classes[] = 'hph-text-primary';
} else {
    $container_classes[] = 'hph-text-white';
}

// Content width classes - Made wider for better hero presence
switch ($content_width) {
    case 'narrow':
        $container_classes[] = 'hph-max-w-4xl';
        break;
    case 'wide':
        $container_classes[] = 'hph-max-w-7xl';
        break;
    case 'full':
        $container_classes[] = 'hph-max-w-none';
        break;
    case 'normal':
    default:
        $container_classes[] = 'hph-max-w-6xl';
        break;
}

// Alignment classes
switch ($alignment) {
    case 'left':
        $container_classes[] = 'hph-text-left';
        $container_classes[] = 'hph-mr-auto';  // This pushes container to the left
        break;
    case 'right':
        $container_classes[] = 'hph-text-right';
        $container_classes[] = 'hph-ml-auto';  // This pushes container to the right
        break;
    case 'center':
    default:
        $container_classes[] = 'hph-text-center';
        $container_classes[] = 'hph-mx-auto';
        break;
}

// Build inline styles
$inline_styles = array();

if ($background_image) {
    $inline_styles[] = "background-image: url('" . esc_url($background_image) . "')";
    $inline_styles[] = "background-size: cover";
    $inline_styles[] = "background-position: center";
    $inline_styles[] = "background-repeat: no-repeat";
    if ($parallax) {
        $inline_styles[] = "background-attachment: fixed";
    }
}

$style_attr = !empty($inline_styles) ? 'style="' . implode('; ', $inline_styles) . '"' : '';

// Ensure Font Awesome is loaded for icons
if (!wp_script_is('font-awesome', 'enqueued')) {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
}
?>

<section 
    class="<?php echo esc_attr(implode(' ', $hero_classes)); ?>"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    <?php echo $style_attr; ?>
>
    
    <?php if ($background_video): ?>
    <!-- Video Background -->
    <video 
        class="hph-hero-video hph-absolute hph-inset-0 hph-w-full hph-h-full hph-object-cover hph-z-0"
        autoplay 
        muted 
        loop 
        playsinline
        <?php if ($background_image): ?>poster="<?php echo esc_url($background_image); ?>"<?php endif; ?>
    >
        <source src="<?php echo esc_url($background_video); ?>" type="video/mp4">
        <?php if ($background_image): ?>
        <!-- Fallback image for browsers that don't support video -->
        <img src="<?php echo esc_url($background_image); ?>" alt="Hero background" class="hph-w-full hph-h-full hph-object-cover">
        <?php endif; ?>
    </video>
    <?php endif; ?>
    
    <?php if ($overlay !== 'none'): ?>
    <!-- Overlay -->
    <div class="hph-hero-overlay hph-absolute hph-inset-0 hph-z-5"></div>
    <?php endif; ?>
    
    <!-- Content Container -->
    <div class="hph-hero-container hph-relative hph-z-10 hph-w-full hph-px-xl hph-md:px-2xl hph-lg:px-3xl hph-flex <?php echo $alignment === 'right' ? 'hph-justify-end' : ($alignment === 'left' ? 'hph-justify-start' : ''); ?>">
        <div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>">
            
            <?php if ($badge): ?>
            <!-- Badge -->
            <div class="hph-mb-lg hph-animate-slide-down">
                <span class="hph-hero-badge hph-inline-flex hph-items-center hph-px-lg hph-py-md hph-rounded-full hph-bg-white hph-bg-opacity-20 hph-text-white hph-text-sm hph-font-semibold hph-backdrop-blur hph-shadow-lg">
                    <?php if ($badge_icon): ?>
                    <i class="<?php echo esc_attr($badge_icon); ?> hph-mr-sm"></i>
                    <?php endif; ?>
                    <?php echo esc_html($badge); ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if ($headline): ?>
            <!-- Hero Headline -->
            <h1 class="hph-hero-headline hph-mb-xl hph-text-3xl hph-md:text-4xl hph-lg:text-5xl hph-font-bold hph-leading-tight <?php echo $fade_in ? 'hph-animate-fade-in-up' : ''; ?>">
                <?php echo esc_html($headline); ?>
            </h1>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <!-- Hero Subheadline -->
            <h2 class="hph-hero-subheadline hph-mb-xl hph-text-lg hph-md:text-xl hph-font-medium hph-leading-snug hph-opacity-90 <?php echo $fade_in ? 'hph-animate-fade-in-up hph-delay-100' : ''; ?>">
                <?php echo esc_html($subheadline); ?>
            </h2>
            <?php endif; ?>
            
            <?php if ($content): ?>
            <!-- Hero Content -->
            <div class="hph-hero-content-text hph-mb-2xl hph-text-base hph-md:text-lg hph-leading-normal hph-opacity-85 <?php echo $fade_in ? 'hph-animate-fade-in-up hph-delay-200' : ''; ?>">
                <?php echo wp_kses_post($content); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($style === 'property' && $listing_id): ?>
            <!-- Property-specific content -->
            <div class="hph-property-hero-details hph-mb-2xl <?php echo $fade_in ? 'hph-animate-fade-in-up hph-delay-250' : ''; ?>">
                
                <?php if ($show_price || $show_status): ?>
                <div class="hph-property-meta hph-flex hph-flex-wrap hph-items-center hph-gap-md hph-mb-lg">
                    <?php if ($show_price && function_exists('hpt_get_listing_price_formatted')): ?>
                        <?php $price = hpt_get_listing_price_formatted($listing_id); ?>
                        <?php if ($price): ?>
                        <div class="hph-property-price hph-text-2xl hph-md:text-3xl hph-font-bold hph-text-white">
                            <?php echo esc_html($price); ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if ($show_status && function_exists('hpt_get_listing_status')): ?>
                        <?php $status = hpt_get_listing_status($listing_id); ?>
                        <?php if ($status): ?>
                        <span class="hph-property-status hph-badge hph-badge-lg hph-badge-white hph-opacity-90">
                            <?php echo esc_html($status); ?>
                        </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($show_stats): ?>
                <div class="hph-property-stats hph-flex hph-flex-wrap hph-gap-lg hph-text-white hph-opacity-90">
                    <?php
                    // Get property stats with null safety
                    $bedrooms = function_exists('hpt_get_listing_bedrooms') ? hpt_get_listing_bedrooms($listing_id) : '';
                    $bathrooms = function_exists('hpt_get_listing_bathrooms') ? hpt_get_listing_bathrooms($listing_id) : '';
                    $sqft = function_exists('hpt_get_listing_square_footage') ? hpt_get_listing_square_footage($listing_id) : '';
                    
                    if ($bedrooms): ?>
                    <div class="hph-stat-item hph-flex hph-items-center hph-gap-sm">
                        <svg class="hph-w-5 hph-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10v11M20 10v11"></path>
                        </svg>
                        <span class="hph-font-semibold"><?php echo esc_html($bedrooms); ?></span>
                        <span><?php echo _n('Bed', 'Beds', intval($bedrooms), 'happy-place-theme'); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($bathrooms): ?>
                    <div class="hph-stat-item hph-flex hph-items-center hph-gap-sm">
                        <svg class="hph-w-5 hph-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10v11M20 10v11"></path>
                        </svg>
                        <span class="hph-font-semibold"><?php echo esc_html($bathrooms); ?></span>
                        <span><?php echo _n('Bath', 'Baths', floatval($bathrooms), 'happy-place-theme'); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($sqft): ?>
                    <div class="hph-stat-item hph-flex hph-items-center hph-gap-sm">
                        <svg class="hph-w-5 hph-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 3V1M7 7v1M7 11v1M15 21a4 4 0 004-4V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4zM15 3V1M15 7v1M15 11v1"></path>
                        </svg>
                        <span class="hph-font-semibold"><?php echo esc_html($sqft); ?></span>
                        <span><?php _e('Sq Ft', 'happy-place-theme'); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
            </div>
            <?php endif; ?>
            
            <?php if (!empty($buttons)): ?>
            <!-- Hero Buttons -->
            <div class="hph-hero-buttons hph-flex hph-flex-wrap hph-gap-lg hph-items-center <?php echo $alignment === 'center' ? 'hph-justify-center' : ($alignment === 'right' ? 'hph-justify-end' : 'hph-justify-start'); ?> <?php echo $fade_in ? 'hph-animate-fade-in-up hph-delay-300' : ''; ?>">
                <?php foreach ($buttons as $index => $button): 
                    $btn_defaults = array(
                        'text' => 'Button',
                        'url' => '#',
                        'style' => 'white',
                        'size' => 'xl',
                        'icon' => '',
                        'icon_position' => 'left',
                        'target' => '_self'
                    );
                    $btn = wp_parse_args($button, $btn_defaults);
                    
                    $btn_classes = array(
                        'hph-btn',
                        'hph-btn-' . $btn['style'],
                        'hph-btn-' . $btn['size'],
                        'hph-transition-all',
                        'hph-duration-300',
                        'hph-hero-btn',
                        'hph-shadow-lg',
                        'hph-hover-lift'
                    );
                ?>
                <a 
                    href="<?php echo esc_url($btn['url']); ?>"
                    class="<?php echo esc_attr(implode(' ', $btn_classes)); ?>"
                    <?php if ($btn['target'] !== '_self'): ?>target="<?php echo esc_attr($btn['target']); ?>"<?php endif; ?>
                >
                    <?php if ($btn['icon'] && $btn['icon_position'] === 'left'): ?>
                    <i class="<?php echo esc_attr($btn['icon']); ?> hph-mr-sm"></i>
                    <?php endif; ?>
                    <span><?php echo esc_html($btn['text']); ?></span>
                    <?php if ($btn['icon'] && $btn['icon_position'] === 'right'): ?>
                    <i class="<?php echo esc_attr($btn['icon']); ?> hph-ml-sm"></i>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    
    <?php if ($scroll_indicator): ?>
    <!-- Scroll Indicator -->
    <div class="hph-hero-scroll hph-absolute hph-bottom-lg hph-left-1/2 hph-transform hph--translate-x-1/2 hph-z-10">
        <div class="hph-scroll-indicator hph-flex hph-flex-col hph-items-center hph-text-white hph-opacity-75 hph-transition-opacity hph-hover-opacity-100">
            <span class="hph-text-sm hph-mb-sm hph-font-medium">Scroll</span>
            <div class="hph-scroll-arrow hph-w-6 hph-h-10 hph-border-2 hph-border-white hph-rounded-full hph-relative">
                <div class="hph-scroll-dot hph-absolute hph-top-2 hph-left-1/2 hph-w-1 hph-h-2 hph-bg-white hph-rounded-full hph-transform hph--translate-x-1/2 hph-animate-bounce"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
</section>

<style>
/* Hero Section Styles */
.hph-hero {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    overflow: hidden;
}

/* Hero Heights */
.hph-hero-sm { min-height: 50vh; }
.hph-hero-md { min-height: 60vh; }
.hph-hero-lg { min-height: 75vh; }
.hph-hero-xl { min-height: 85vh; }
.hph-hero-full { min-height: 100vh; }

/* Hero Styles - REMOVED !important declarations */
.hph-hero-minimal {
    background-color: var(--hph-white);
    color: var(--hph-text-primary);
}

/* Gradient style - only applies if no background image */
.hph-hero-gradient:not(.has-bg-image) {
    background: linear-gradient(135deg, var(--hph-primary) 0%, var(--hph-primary-dark) 100%);
}

/* When gradient style has background image, create gradient overlay with pseudo-element */
.hph-hero-gradient.has-bg-image::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(81, 186, 224, 0.7) 0%, rgba(81, 186, 224, 0.9) 100%);
    z-index: 1;
}

.hph-hero-image {
    background-color: var(--hph-gray-800);
}

.hph-hero-video {
    background-color: var(--hph-gray-900);
}

.hph-hero-split:not(.has-bg-image) {
    background: linear-gradient(90deg, var(--hph-primary) 0%, var(--hph-primary) 50%, var(--hph-gray-50) 50%, var(--hph-gray-50) 100%);
}

/* Parallax Effect */
.hph-hero-parallax {
    background-attachment: fixed;
}

@media (max-width: 768px) {
    .hph-hero-parallax {
        background-attachment: scroll; /* Disable parallax on mobile for performance */
    }
}

/* Overlay Styles - Extended variations */
.hph-hero-overlay {
    pointer-events: none;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 5;
}

.hph-hero-overlay-dark .hph-hero-overlay {
    background-color: rgba(0, 0, 0, var(--overlay-opacity, 0.4));
}

.hph-hero-overlay-light .hph-hero-overlay {
    background-color: rgba(255, 255, 255, var(--overlay-opacity, 0.4));
}

.hph-hero-overlay-gradient .hph-hero-overlay {
    background: linear-gradient(to bottom, 
        rgba(0, 0, 0, 0.8) 0%, 
        rgba(0, 0, 0, 0.2) 50%, 
        rgba(0, 0, 0, 0.8) 100%);
}

.hph-hero-overlay-gradient-reverse .hph-hero-overlay {
    background: linear-gradient(to top, 
        rgba(0, 0, 0, 0.9) 0%, 
        rgba(0, 0, 0, 0.3) 40%, 
        rgba(0, 0, 0, 0.1) 100%);
}

.hph-hero-overlay-gradient-radial .hph-hero-overlay {
    background: radial-gradient(ellipse at center, 
        rgba(0, 0, 0, 0.2) 0%, 
        rgba(0, 0, 0, 0.7) 100%);
}

.hph-hero-overlay-primary .hph-hero-overlay {
    background-color: rgba(81, 186, 224, var(--overlay-opacity, 0.4));
}

.hph-hero-overlay-primary-gradient .hph-hero-overlay {
    background: linear-gradient(135deg, 
        rgba(81, 186, 224, 0.8) 0%, 
        rgba(81, 186, 224, 0.3) 50%, 
        rgba(81, 186, 224, 0.6) 100%);
}

/* Overlay Opacity Variations */
.hph-hero-opacity-20 { --overlay-opacity: 0.2; }
.hph-hero-opacity-40 { --overlay-opacity: 0.4; }
.hph-hero-opacity-60 { --overlay-opacity: 0.6; }
.hph-hero-opacity-80 { --overlay-opacity: 0.8; }

/* Hero Typography */
.hph-hero-headline {
    font-size: var(--hph-text-3xl);
    line-height: var(--hph-leading-tight);
    font-weight: var(--hph-font-bold);
    margin-bottom: var(--hph-space-xl);
    color: inherit;
}

.hph-hero-subheadline {
    font-size: var(--hph-text-lg);
    line-height: var(--hph-leading-snug);
    font-weight: var(--hph-font-medium);
    margin-bottom: var(--hph-space-xl);
    opacity: 0.9;
    color: inherit;
}

.hph-hero-content-text {
    font-size: var(--hph-text-base);
    line-height: var(--hph-leading-normal);
    margin-bottom: var(--hph-space-2xl);
    opacity: 0.85;
    color: inherit;
}

/* Specific color overrides for different hero styles */
.hph-hero-minimal .hph-hero-headline,
.hph-hero-minimal .hph-hero-subheadline,
.hph-hero-minimal .hph-hero-content-text {
    color: var(--hph-primary);
}

.hph-hero-split .hph-hero-headline,
.hph-hero-split .hph-hero-subheadline,
.hph-hero-split .hph-hero-content-text {
    color: var(--hph-white);
}

/* Ensure white text for image heroes with overlays */
.hph-hero-image .hph-hero-headline,
.hph-hero-image .hph-hero-subheadline,
.hph-hero-image .hph-hero-content-text,
.hph-hero-gradient .hph-hero-headline,
.hph-hero-gradient .hph-hero-subheadline,
.hph-hero-gradient .hph-hero-content-text {
    color: var(--hph-white);
}

/* Special handling for light overlays */
.hph-hero-overlay-light + .hph-hero-container .hph-hero-headline,
.hph-hero-overlay-light + .hph-hero-container .hph-hero-subheadline,
.hph-hero-overlay-light + .hph-hero-container .hph-hero-content-text {
    color: var(--hph-primary);
}

/* Hero Badge - Enhanced */
.hph-hero-badge {
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    transition: all 300ms ease;
}

.hph-hero-badge:hover {
    background-color: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

/* Hero Buttons - Enhanced */
.hph-hero-btn {
    font-weight: var(--hph-font-semibold);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--hph-radius-lg);
    transition: all 300ms ease;
    position: relative;
    overflow: hidden;
}

.hph-btn-white {
    background-color: var(--hph-white);
    color: var(--hph-primary);
    border: 2px solid var(--hph-white);
}

.hph-btn-white:hover {
    background-color: transparent;
    color: var(--hph-white);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.hph-btn-outline-white {
    background-color: transparent;
    color: var(--hph-white);
    border: 2px solid var(--hph-white);
}

.hph-btn-outline-white:hover {
    background-color: var(--hph-white);
    color: var(--hph-primary);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(255, 255, 255, 0.2);
}

.hph-btn-primary {
    background-color: var(--hph-primary);
    color: var(--hph-white);
    border: 2px solid var(--hph-primary);
}

.hph-btn-primary:hover {
    background-color: var(--hph-primary-dark);
    border-color: var(--hph-primary-dark);
    transform: translateY(-2px);
}

/* Button sizes */
.hph-btn-s {
    padding: var(--hph-space-sm) var(--hph-space-md);
    font-size: var(--hph-text-sm);
}

.hph-btn-m {
    padding: var(--hph-space-md) var(--hph-space-lg);
    font-size: var(--hph-text-base);
}

.hph-btn-l {
    padding: var(--hph-space-md) var(--hph-space-xl);
    font-size: var(--hph-text-base);
}

.hph-btn-xl {
    padding: var(--hph-space-lg) var(--hph-space-2xl);
    font-size: var(--hph-text-lg);
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
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

.hph-animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
}

.hph-animate-slide-down {
    animation: slideDown 0.6s ease-out forwards;
}

.hph-fade-in {
    animation: fadeIn 1s ease-out forwards;
}

/* Animation delays */
.hph-delay-100 {
    animation-delay: 0.1s;
    opacity: 0;
}

.hph-delay-200 {
    animation-delay: 0.2s;
    opacity: 0;
}

.hph-delay-300 {
    animation-delay: 0.3s;
    opacity: 0;
}

/* Hover lift effect */
.hph-hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hph-hover-lift:hover {
    transform: translateY(-2px);
}

/* Scroll Indicator Animation */
@keyframes scroll-bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0) translateX(-50%);
    }
    40% {
        transform: translateY(-10px) translateX(-50%);
    }
    60% {
        transform: translateY(-5px) translateX(-50%);
    }
}

.hph-scroll-dot {
    animation: scroll-bounce 2s infinite;
}

/* Responsive Typography */
@media (min-width: 768px) {
    .hph-hero-headline {
        font-size: var(--hph-text-4xl);
    }
    
    .hph-hero-subheadline {
        font-size: var(--hph-text-xl);
    }
    
    .hph-hero-content-text {
        font-size: var(--hph-text-lg);
    }
}

@media (min-width: 1024px) {
    .hph-hero-headline {
        font-size: var(--hph-text-5xl);
    }
}

/* Utility Classes */
.hph-relative { position: relative; }
.hph-absolute { position: absolute; }
.hph-inset-0 { top: 0; right: 0; bottom: 0; left: 0; }
.hph-z-0 { z-index: 0; }
.hph-z-5 { z-index: 5; }
.hph-z-10 { z-index: 10; }
.hph-w-full { width: 100%; }
.hph-h-full { height: 100%; }
.hph-flex { display: flex; }
.hph-inline-flex { display: inline-flex; }
.hph-items-center { align-items: center; }
.hph-justify-center { justify-content: center; }
.hph-justify-start { justify-content: flex-start; }
.hph-justify-end { justify-content: flex-end; }
.hph-flex-col { flex-direction: column; }
.hph-flex-wrap { flex-wrap: wrap; }
.hph-object-cover { object-fit: cover; }
.hph-overflow-hidden { overflow: hidden; }
.hph-text-white { color: var(--hph-white); }
.hph-text-primary { color: var(--hph-primary); }
.hph-bg-white { background-color: var(--hph-white); }
.hph-bg-opacity-20 { background-color: rgba(255, 255, 255, 0.2); }
.hph-opacity-75 { opacity: 0.75; }
.hph-opacity-85 { opacity: 0.85; }
.hph-opacity-90 { opacity: 0.9; }
.hph-backdrop-blur { backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
.hph-animate-bounce { animation: bounce 1s infinite; }
.hph-transition-all { transition: all 300ms ease; }
.hph-transition-opacity { transition: opacity 300ms ease; }
.hph-hover-opacity-100:hover { opacity: 1; }
.hph-duration-300 { transition-duration: 300ms; }
.hph-transform { transform: translateZ(0); }
.hph--translate-x-1/2 { transform: translateX(-50%); }
.hph-rounded-full { border-radius: 9999px; }
.hph-border-2 { border-width: 2px; }
.hph-border-white { border-color: var(--hph-white); }
.hph-shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }

/* Max Width Classes */
.hph-max-w-4xl { max-width: 56rem; }
.hph-max-w-6xl { max-width: 72rem; }
.hph-max-w-7xl { max-width: 80rem; }
.hph-max-w-none { max-width: none; }

/* Positioning Classes */
.hph-bottom-lg { bottom: var(--hph-space-lg); }
.hph-left-1/2 { left: 50%; }
.hph-top-2 { top: 0.5rem; }
.hph-w-1 { width: 0.25rem; }
.hph-w-6 { width: 1.5rem; }
.hph-h-2 { height: 0.5rem; }
.hph-h-10 { height: 2.5rem; }

/* Padding Classes */
.hph-px-xl { padding-left: var(--hph-space-xl); padding-right: var(--hph-space-xl); }
.hph-px-2xl { padding-left: var(--hph-space-2xl); padding-right: var(--hph-space-2xl); }
.hph-px-3xl { padding-left: var(--hph-space-3xl); padding-right: var(--hph-space-3xl); }
.hph-px-lg { padding-left: var(--hph-space-lg); padding-right: var(--hph-space-lg); }
.hph-py-md { padding-top: var(--hph-space-md); padding-bottom: var(--hph-space-md); }

/* Responsive Padding Classes */
@media (min-width: 768px) {
    .hph-md\:px-2xl { padding-left: var(--hph-space-2xl); padding-right: var(--hph-space-2xl); }
    .hph-md\:text-lg { font-size: var(--hph-text-lg); }
    .hph-md\:text-xl { font-size: var(--hph-text-xl); }
    .hph-md\:text-4xl { font-size: var(--hph-text-4xl); }
}

@media (min-width: 1024px) {
    .hph-lg\:px-3xl { padding-left: var(--hph-space-3xl); padding-right: var(--hph-space-3xl); }
    .hph-lg\:text-5xl { font-size: var(--hph-text-5xl); }
}

/* Line Height Utilities */
.hph-leading-tight { line-height: var(--hph-leading-tight); }
.hph-leading-snug { line-height: var(--hph-leading-snug); }
.hph-leading-normal { line-height: var(--hph-leading-normal); }

/* Spacing Utilities */
.hph-mb-sm { margin-bottom: var(--hph-space-sm); }
.hph-mb-lg { margin-bottom: var(--hph-space-lg); }
.hph-mb-xl { margin-bottom: var(--hph-space-xl); }
.hph-mb-2xl { margin-bottom: var(--hph-space-2xl); }
.hph-mr-sm { margin-right: var(--hph-space-sm); }
.hph-ml-sm { margin-left: var(--hph-space-sm); }
.hph-gap-lg { gap: var(--hph-space-lg); }

/* Margin Utilities for Alignment */
.hph-mx-0 { margin-left: 0; margin-right: 0; }
.hph-mx-auto { margin-left: auto; margin-right: auto; }
.hph-ml-auto { margin-left: auto; }
.hph-mr-auto { margin-right: auto; }

/* Text Alignment */
.hph-text-left { text-align: left; }
.hph-text-center { text-align: center; }
.hph-text-right { text-align: right; }

/* Font Utilities */
.hph-text-sm { font-size: var(--hph-text-sm); }
.hph-text-base { font-size: var(--hph-text-base); }
.hph-text-lg { font-size: var(--hph-text-lg); }
.hph-text-xl { font-size: var(--hph-text-xl); }
.hph-text-3xl { font-size: var(--hph-text-3xl); }
.hph-text-4xl { font-size: var(--hph-text-4xl); }
.hph-text-5xl { font-size: var(--hph-text-5xl); }
.hph-font-medium { font-weight: var(--hph-font-medium); }
.hph-font-semibold { font-weight: var(--hph-font-semibold); }
.hph-font-bold { font-weight: var(--hph-font-bold); }

/* Ensure content appears above gradient overlay */
.hph-hero-gradient.has-bg-image .hph-hero-container {
    position: relative;
    z-index: 10;
}

/* Smooth scrolling for anchor links */
html {
    scroll-behavior: smooth;
}

/* Performance optimizations */
.hph-hero-video {
    will-change: transform;
}

.hph-hero-parallax {
    will-change: transform;
}

/* Accessibility improvements */
.hph-hero-btn:focus {
    outline: 2px solid var(--hph-primary);
    outline-offset: 2px;
}

.hph-hero-btn:focus:not(:focus-visible) {
    outline: none;
}

/* Print styles */
@media print {
    .hph-hero {
        min-height: auto;
        padding: 2rem 0;
    }
    
    .hph-hero-video,
    .hph-scroll-indicator {
        display: none;
    }
}
</style>