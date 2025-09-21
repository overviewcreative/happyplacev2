<?php
/**
 * HPH Hero Section Template
 * 
 * Hero section template using base CSS variables and inline styles
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/hero');
}

// Default arguments
$defaults = array(
    'style' => 'gradient',
    'theme' => '', // Color theme: 'primary', 'secondary', 'accent', 'success', 'info', 'warning', 'danger', 'ocean', 'sunset', 'forest', 'dark', 'light'
    'height' => 'lg',
    'background_image' => '',
    'background_video' => '',
    'overlay' => 'dark',
    'overlay_opacity' => '40',
    'gradient_overlay' => '', // CSS variable name for gradient overlay
    'alignment' => 'center',
    'content_width' => 'normal',
    'badge' => '',
    'badge_icon' => '',
    'headline' => 'Hero Section',
    'headline_prefix' => '', // Text before rotating part
    'headline_suffix' => '', // Text after rotating part
    'rotating_words' => array(), // Array of words/phrases to rotate
    'rotation_type' => 'typing', // Options: 'typing', 'fade', 'slide', 'flip'
    'rotation_speed' => 3000, // Milliseconds between rotations
    'typing_speed' => 100, // Milliseconds per character (for typing effect)
    'subheadline' => '',
    'content' => '',
    'meta' => array(), // Meta information array (author, date, read_time, etc.)
    'buttons' => array(),
    'scroll_indicator' => false,
    'section_id' => '',
    'parallax' => false,
    'fade_in' => false,
    'listing_id' => 0,
    'show_gallery' => false,
    'show_status' => false,
    'show_price' => false,
    'show_stats' => false,
    'is_top_of_page' => false, // Add 20vh padding when this is the first section on page
    // New blur and animation effects
    'backdrop_blur' => false, // Enable backdrop blur on overlay
    'backdrop_blur_intensity' => 'md', // sm, md, lg, xl
    'content_animation' => 'fade-up', // fade-up, slide-up, zoom-in, bounce-in, none
    'animation_delay' => 0, // Delay in milliseconds
    'animation_duration' => 800, // Duration in milliseconds
    'parallax_intensity' => 'normal', // subtle, normal, strong
    'ken_burns' => false, // Enable Ken Burns effect on background image
    'ken_burns_direction' => 'zoom-in', // zoom-in, zoom-out, pan-left, pan-right, zoom-pan
    'ken_burns_duration' => 20, // Duration in seconds
);

// Merge with provided args - use consistent null coalescing
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Auto-fix: Switch to image style if background_image is provided
if (!empty($background_image) && $style === 'gradient') {
    $style = 'image';
}

// Theme configuration - sets colors for all components
$theme_config = array();
if (!empty($theme)) {
    switch($theme) {
        case 'primary':
            $theme_config = array(
                'gradient_bg' => 'var(--hph-gradient-primary)',
                'solid_bg' => 'var(--hph-primary)',
                'text_color' => 'var(--hph-white)',
                'badge_bg' => 'rgba(255, 255, 255, 0.2)',
                'badge_color' => 'var(--hph-white)',
                'button_primary' => 'white',
                'button_secondary' => 'outline-white',
                'overlay_gradient' => 'primary-overlay'
            );
            break;
        case 'secondary':
            $theme_config = array(
                'gradient_bg' => 'var(--hph-gradient-secondary)',
                'solid_bg' => 'var(--hph-secondary)',
                'text_color' => 'var(--hph-white)',
                'badge_bg' => 'rgba(255, 255, 255, 0.2)',
                'badge_color' => 'var(--hph-white)',
                'button_primary' => 'white',
                'button_secondary' => 'outline-white',
                'overlay_gradient' => 'secondary-overlay'
            );
            break;
        case 'accent':
            $theme_config = array(
                'gradient_bg' => 'var(--hph-gradient-accent)',
                'solid_bg' => 'var(--hph-accent)',
                'text_color' => 'var(--hph-white)',
                'badge_bg' => 'rgba(255, 255, 255, 0.2)',
                'badge_color' => 'var(--hph-white)',
                'button_primary' => 'white',
                'button_secondary' => 'outline-white',
                'overlay_gradient' => 'accent-overlay'
            );
            break;
        case 'ocean':
            $theme_config = array(
                'gradient_bg' => 'var(--hph-gradient-ocean)',
                'solid_bg' => '#1e3a8a',
                'text_color' => 'var(--hph-white)',
                'badge_bg' => 'rgba(255, 255, 255, 0.15)',
                'badge_color' => 'var(--hph-white)',
                'button_primary' => 'white',
                'button_secondary' => 'outline-white',
                'overlay_gradient' => 'overlay-dark'
            );
            break;
        case 'sunset':
            $theme_config = array(
                'gradient_bg' => 'var(--hph-gradient-sunset)',
                'solid_bg' => 'var(--hph-secondary)',
                'text_color' => 'var(--hph-white)',
                'badge_bg' => 'rgba(255, 255, 255, 0.2)',
                'badge_color' => 'var(--hph-white)',
                'button_primary' => 'white',
                'button_secondary' => 'outline-white',
                'overlay_gradient' => 'overlay-dark'
            );
            break;
        case 'forest':
            $theme_config = array(
                'gradient_bg' => 'var(--hph-gradient-forest)',
                'solid_bg' => '#14532d',
                'text_color' => 'var(--hph-white)',
                'badge_bg' => 'rgba(255, 255, 255, 0.15)',
                'badge_color' => 'var(--hph-white)',
                'button_primary' => 'white',
                'button_secondary' => 'outline-white',
                'overlay_gradient' => 'overlay-dark'
            );
            break;
        case 'success':
            $theme_config = array(
                'gradient_bg' => 'linear-gradient(135deg, var(--hph-success) 0%, var(--hph-success-dark) 100%)',
                'solid_bg' => 'var(--hph-success)',
                'text_color' => 'var(--hph-white)',
                'badge_bg' => 'rgba(255, 255, 255, 0.2)',
                'badge_color' => 'var(--hph-white)',
                'button_primary' => 'white',
                'button_secondary' => 'outline-white',
                'overlay_gradient' => 'success-overlay'
            );
            break;
        case 'info':
            $theme_config = array(
                'gradient_bg' => 'linear-gradient(135deg, var(--hph-info) 0%, var(--hph-info-dark) 100%)',
                'solid_bg' => 'var(--hph-info)',
                'text_color' => 'var(--hph-white)',
                'badge_bg' => 'rgba(255, 255, 255, 0.2)',
                'badge_color' => 'var(--hph-white)',
                'button_primary' => 'white',
                'button_secondary' => 'outline-white',
                'overlay_gradient' => 'info-overlay'
            );
            break;
        case 'dark':
            $theme_config = array(
                'gradient_bg' => 'linear-gradient(135deg, var(--hph-gray-900) 0%, var(--hph-black) 100%)',
                'solid_bg' => 'var(--hph-gray-900)',
                'text_color' => 'var(--hph-white)',
                'badge_bg' => 'rgba(255, 255, 255, 0.1)',
                'badge_color' => 'var(--hph-white)',
                'button_primary' => 'primary',
                'button_secondary' => 'outline-white',
                'overlay_gradient' => 'overlay-dark'
            );
            break;
        case 'light':
            $theme_config = array(
                'gradient_bg' => 'linear-gradient(135deg, var(--hph-white) 0%, var(--hph-gray-100) 100%)',
                'solid_bg' => 'var(--hph-white)',
                'text_color' => 'var(--hph-gray-900)',
                'badge_bg' => 'var(--hph-primary-100)',
                'badge_color' => 'var(--hph-primary-700)',
                'button_primary' => 'primary',
                'button_secondary' => 'outline-primary',
                'overlay_gradient' => 'overlay-light-subtle'
            );
            break;
    }
    
    // Apply theme to style if gradient
    if (!empty($theme_config) && $style === 'gradient' && empty($background_image)) {
        // Will be applied in background styles section
    }
    
    // Override gradient overlay if theme specifies
    if (!empty($theme_config['overlay_gradient']) && empty($gradient_overlay)) {
        $gradient_overlay = $theme_config['overlay_gradient'];
    }
}

// Property-specific auto-configuration
if ($style === 'property' && $listing_id) {
    if (function_exists('hpt_get_listing_title') && empty($headline)) {
        $headline = hpt_get_listing_title($listing_id);
    }
    if (function_exists('hpt_get_listing_address') && empty($subheadline)) {
        $subheadline = hpt_get_listing_address($listing_id);
    }
    if (function_exists('hpt_get_listing_featured_image') && empty($background_image)) {
        $background_image = hpt_get_listing_featured_image($listing_id);
    }
    if (!empty($background_image)) {
        $style = 'image';
    }
}

// Build inline styles for hero section
$hero_styles = array();

// Essential layout styles
$hero_styles[] = 'position: relative';
$hero_styles[] = 'display: flex';
$hero_styles[] = 'align-items: center';
$hero_styles[] = 'justify-content: center';
$hero_styles[] = 'width: 100%';
$hero_styles[] = 'overflow: hidden';

// Height styles using CSS variables
switch ($height) {
    case 'sm':
        $hero_styles[] = 'min-height: 50vh';
        break;
    case 'md':
        $hero_styles[] = 'min-height: 60vh';
        break;
    case 'lg':
        $hero_styles[] = 'min-height: 75vh';
        break;
    case 'xl':
        $hero_styles[] = 'min-height: 85vh';
        break;
    case 'full':
        $hero_styles[] = 'min-height: 100vh';
        break;
}

// Background styles
if ($background_image) {
    // Get overlay color for smooth loading background
    $loading_bg = '';
    if (!empty($theme_config['solid_bg'])) {
        $loading_bg = $theme_config['solid_bg'];
    } else {
        // Default loading background based on overlay type
        switch ($overlay) {
            case 'dark':
            case 'dark-subtle':
            case 'dark-heavy':
                $loading_bg = 'var(--hph-gray-900)';
                break;
            case 'light':
            case 'light-subtle':
                $loading_bg = 'var(--hph-gray-100)';
                break;
            case 'gradient':
            case 'primary':
            case 'primary-gradient':
                $loading_bg = 'var(--hph-primary)';
                break;
            default:
                $loading_bg = 'var(--hph-gray-800)';
                break;
        }
    }

    $hero_styles[] = "background: " . $loading_bg; // Start with solid color
    $hero_styles[] = "background-image: url('" . esc_url($background_image) . "')";
    $hero_styles[] = "background-size: cover";
    $hero_styles[] = "background-position: center";
    $hero_styles[] = "background-repeat: no-repeat";
    $hero_styles[] = "transition: background-color 0.5s ease-out"; // Smooth transition

    if ($parallax) {
        $hero_styles[] = "background-attachment: fixed";
    }
} elseif ($style === 'gradient') {
    // Use theme gradient if available, otherwise default
    if (!empty($theme_config['gradient_bg'])) {
        $hero_styles[] = "background: " . $theme_config['gradient_bg'];
    } else {
        $hero_styles[] = "background: var(--hph-gradient-primary)";
    }
} elseif ($style === 'solid' && !empty($theme_config['solid_bg'])) {
    $hero_styles[] = "background: " . $theme_config['solid_bg'];
}

// Apply theme text color if set
if (!empty($theme_config['text_color'])) {
    $hero_styles[] = "color: " . $theme_config['text_color'];
}

$hero_style_attr = !empty($hero_styles) ? 'style="' . implode('; ', $hero_styles) . '"' : '';

// Build overlay styles
$overlay_styles = array();

// Add backdrop blur if enabled
$backdrop_blur_style = '';
if ($backdrop_blur) {
    switch ($backdrop_blur_intensity) {
        case 'sm':
            $backdrop_blur_style = 'backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);';
            break;
        case 'md':
            $backdrop_blur_style = 'backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);';
            break;
        case 'lg':
            $backdrop_blur_style = 'backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);';
            break;
        case 'xl':
            $backdrop_blur_style = 'backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);';
            break;
        default:
            $backdrop_blur_style = 'backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);';
    }
}

// If a gradient_overlay CSS variable is specified, use it directly
if (!empty($gradient_overlay)) {
    $overlay_styles[] = 'background: var(--hph-gradient-' . $gradient_overlay . ')';
} else {
    // Fallback to overlay type with opacity
    switch ($overlay) {
        case 'dark':
            $overlay_styles[] = 'background: var(--hph-gradient-overlay-dark)';
            break;
        case 'dark-subtle':
            $overlay_styles[] = 'background: var(--hph-gradient-overlay-dark-subtle)';
            break;
        case 'dark-heavy':
            $overlay_styles[] = 'background: var(--hph-gradient-overlay-dark-heavy)';
            break;
        case 'light':
            $overlay_styles[] = 'background: var(--hph-gradient-overlay-light)';
            break;
        case 'light-subtle':
            $overlay_styles[] = 'background: var(--hph-gradient-overlay-light-subtle)';
            break;
        case 'gradient':
            $overlay_styles[] = 'background: var(--hph-gradient-primary-overlay)';
            break;
        case 'gradient-reverse':
            $overlay_styles[] = 'background: var(--hph-gradient-overlay-dark-reverse)';
            break;
        case 'gradient-radial':
            $overlay_styles[] = 'background: var(--hph-gradient-overlay-radial-dark)';
            break;
        case 'primary':
            $overlay_styles[] = 'background: var(--hph-gradient-primary-overlay)';
            break;
        case 'primary-gradient':
            $overlay_styles[] = 'background: var(--hph-gradient-primary-overlay)';
            break;
        case 'hero':
            $overlay_styles[] = 'background: var(--hph-gradient-hero-overlay)';
            break;
        case 'scrim-top':
            $overlay_styles[] = 'background: var(--hph-gradient-scrim-top)';
            break;
        case 'scrim-bottom':
            $overlay_styles[] = 'background: var(--hph-gradient-scrim-bottom)';
            break;
        case 'diagonal':
            $overlay_styles[] = 'background: var(--hph-gradient-diagonal-dark)';
            break;
        case 'custom':
            // For backward compatibility with opacity parameter
            $opacity_value = intval($overlay_opacity) / 100;
            $overlay_styles[] = 'background: rgba(0, 0, 0, ' . $opacity_value . ')';
            break;
    }
}

// Add backdrop blur to overlay styles
if ($backdrop_blur_style) {
    $overlay_styles[] = $backdrop_blur_style;
}

$overlay_style_attr = !empty($overlay_styles) ? 'style="' . implode('; ', $overlay_styles) . '"' : '';

// Content container width
$container_max_width = '';
switch ($content_width) {
    case 'narrow':
        $container_max_width = 'max-width: var(--hph-container-md);';
        break;
    case 'wide':
        $container_max_width = 'max-width: var(--hph-container-2xl);';
        break;
    case 'full':
        $container_max_width = 'max-width: 100%; padding-left: 0; padding-right: 0;';
        break;
    case 'normal':
    default:
        $container_max_width = 'max-width: var(--hph-container-xl);';
        break;
}

// Text alignment styles
$text_align_style = '';
$content_justify = '';
switch ($alignment) {
    case 'left':
        $text_align_style = 'text-align: left;';
        $content_justify = 'align-items: flex-start;';
        break;
    case 'right':
        $text_align_style = 'text-align: right;';
        $content_justify = 'align-items: flex-end;';
        break;
    case 'center':
    default:
        $text_align_style = 'text-align: center;';
        $content_justify = 'align-items: center;';
        break;
}

// Ensure Font Awesome is loaded for icons
if (!wp_script_is('font-awesome', 'enqueued')) {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
}
?>

<section 
    class="hph-hero hph-hero-<?php echo esc_attr($style); ?> <?php echo $background_image ? 'has-bg-image' : ''; ?> <?php echo $parallax ? 'hph-hero-parallax' : ''; ?>"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    data-bg="<?php echo esc_attr($style === 'gradient' ? 'gradient' : ($style === 'minimal' ? 'light' : 'dark')); ?>"
    <?php echo $hero_style_attr; ?>
    data-hero-style="<?php echo esc_attr($style); ?>"
>
    
    <?php if ($background_video): ?>
    <!-- Video Background -->
    <video 
        class="hph-hero-video"
        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;"
        autoplay 
        muted 
        loop 
        playsinline
        <?php if ($background_image): ?>poster="<?php echo esc_url($background_image); ?>"<?php endif; ?>
    >
        <source src="<?php echo esc_url($background_video); ?>" type="video/mp4">
        <?php if ($background_image): ?>
        <img src="<?php echo esc_url($background_image); ?>" alt="Hero background" style="width: 100%; height: 100%; object-fit: cover;">
        <?php endif; ?>
    </video>
    <?php endif; ?>
    
    <?php if ($overlay !== 'none'): ?>
    <!-- Overlay -->
    <div class="hph-hero-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1; pointer-events: none; <?php echo implode('; ', $overlay_styles); ?>"></div>
    <?php endif; ?>
    
    <?php if ($background_image && $ken_burns): ?>
    <!-- Ken Burns Background Image -->
    <div class="hph-ken-burns-container" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 0; overflow: hidden;">
        <div class="hph-ken-burns-image hph-ken-burns-<?php echo esc_attr($ken_burns_direction); ?>"
             style="position: absolute; top: -10%; left: -10%; width: 120%; height: 120%;
             background-image: url('<?php echo esc_url($background_image); ?>');
             background-size: cover; background-position: center; background-repeat: no-repeat;
             animation: kenBurns<?php echo ucfirst(str_replace('-', '', $ken_burns_direction)); ?> <?php echo intval($ken_burns_duration); ?>s ease-in-out infinite alternate;">
        </div>
    </div>
    <?php endif; ?>

    <!-- Content Container -->
    <div class="hph-hero-container <?php echo $content_animation !== 'none' ? 'hph-animate-' . esc_attr($content_animation) : ''; ?>"
         style="position: relative; z-index: 2; width: 100%; padding: <?php echo $args['is_top_of_page'] ? '5vh' : 'var(--hph-space-8)'; ?> var(--hph-space-6) var(--hph-space-8) var(--hph-space-6);
         <?php if ($content_animation !== 'none'): ?>
         animation-delay: <?php echo intval($animation_delay); ?>ms;
         animation-duration: <?php echo intval($animation_duration); ?>ms;
         <?php endif; ?>">
        <div class="hph-hero-inner" style="<?php echo $container_max_width; ?> margin-left: auto; margin-right: auto;">
            <div class="hph-hero-content"
                 style="display: flex; flex-direction: column; <?php echo $content_justify; ?> gap: var(--hph-gap-lg); <?php echo $text_align_style; ?>">
                
                <?php if ($badge): ?>
                <!-- Badge -->
                <div style="margin-bottom: var(--hph-space-2);">
                    <?php 
                    $badge_bg = !empty($theme_config['badge_bg']) ? $theme_config['badge_bg'] : 'rgba(255, 255, 255, 0.2)';
                    $badge_color = !empty($theme_config['badge_color']) ? $theme_config['badge_color'] : 'currentColor';
                    ?>
                    <span style="display: inline-flex; align-items: center; gap: var(--hph-gap-sm); padding: var(--hph-space-2) var(--hph-space-4); background: <?php echo $badge_bg; ?>; color: <?php echo $badge_color; ?>; backdrop-filter: blur(10px); border-radius: var(--hph-radius-full); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold);">
                        <?php if ($badge_icon): ?>
                        <i class="<?php echo esc_attr($badge_icon); ?>"></i>
                        <?php endif; ?>
                        <?php echo esc_html($badge); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($headline || (!empty($rotating_words) && ($headline_prefix || $headline_suffix))): ?>
                <!-- Hero Headline -->
                <?php if (!empty($rotating_words) && ($headline_prefix || $headline_suffix)): ?>
                    <!-- Rotating Headline -->
                    <h1 class="hph-hero-headline hph-rotating-headline <?php echo $fade_in ? 'hph-animate-fade-in-up' : ''; ?>" 
                        style="margin: 0 0 var(--hph-space-4) 0; font-size: var(--hph-text-5xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight); <?php echo $fade_in ? 'animation-delay: 0s;' : ''; ?>"
                        data-rotation-type="<?php echo esc_attr($rotation_type); ?>"
                        data-rotation-speed="<?php echo esc_attr($rotation_speed); ?>"
                        data-typing-speed="<?php echo esc_attr($typing_speed); ?>">
                        <?php if ($headline_prefix): ?>
                            <span class="hph-headline-prefix"><?php echo esc_html($headline_prefix); ?></span>
                        <?php endif; ?>
                        <span class="hph-rotating-text-container" style="position: relative; display: inline-block; min-width: 200px; text-align: left;">
                            <span class="hph-rotating-text" 
                                  data-words="<?php echo esc_attr(json_encode($rotating_words)); ?>"
                                  style="display: inline-block;">
                                <?php echo esc_html($rotating_words[0] ?? ''); ?>
                            </span>
                            <?php if ($rotation_type === 'typing'): ?>
                            <span class="hph-typing-cursor" style="display: inline-block; width: 3px; height: 1.2em; background-color: currentColor; animation: blink 1s infinite; margin-left: 2px; vertical-align: text-bottom;"></span>
                            <?php endif; ?>
                        </span>
                        <?php if ($headline_suffix): ?>
                            <span class="hph-headline-suffix"><?php echo esc_html($headline_suffix); ?></span>
                        <?php endif; ?>
                    </h1>
                <?php elseif ($headline): ?>
                    <!-- Static Headline -->
                    <h1 class="hph-hero-headline <?php echo $fade_in ? 'hph-animate-fade-in-up' : ''; ?>" 
                        style="margin: 0 0 var(--hph-space-2) 0; font-size: var(--hph-text-5xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight); <?php echo $fade_in ? 'animation-delay: 0s;' : ''; ?>">
                        <?php echo esc_html($headline); ?>
                    </h1>
                <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($subheadline): ?>
                <!-- Hero Subheadline -->
                <h2 class="hph-hero-subheadline <?php echo $fade_in ? 'hph-animate-fade-in-up' : ''; ?>" 
                    style="margin: 0 var(--hph-space-6) var(--hph-space-2) 0; max-width: 700px; font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); line-height: var(--hph-leading-snug); opacity: 0.9; <?php echo $fade_in ? 'animation-delay: 0.1s;' : ''; ?>">
                    <?php echo esc_html($subheadline); ?>
                </h2>
                <?php endif; ?>
                
                <?php if (!empty($meta) && is_array($meta)): ?>
                <!-- Hero Meta Information -->
                <div class="hph-hero-meta <?php echo $fade_in ? 'hph-animate-fade-in-up' : ''; ?>" 
                     style="margin: 0 var(--hph-space-6) var(--hph-space-2) 0; display: flex; align-items: center; justify-content: <?php echo $alignment === 'center' ? 'center' : ($alignment === 'right' ? 'flex-end' : 'flex-start'); ?>; gap: var(--hph-gap-lg); flex-wrap: wrap; font-size: var(--hph-text-sm); opacity: 0.8; <?php echo $fade_in ? 'animation-delay: 0.15s;' : ''; ?>">
                    <?php foreach ($meta as $key => $value): ?>
                        <?php if (!empty($value)): ?>
                            <span class="hph-meta-item" style="display: flex; align-items: center; gap: var(--hph-gap-xs);">
                                <?php if ($key === 'author'): ?>
                                    <i class="fas fa-user" style="opacity: 0.7;"></i>
                                    <span>By <?php echo esc_html($value); ?></span>
                                <?php elseif ($key === 'date'): ?>
                                    <i class="fas fa-calendar" style="opacity: 0.7;"></i>
                                    <span><?php echo esc_html($value); ?></span>
                                <?php elseif ($key === 'read_time'): ?>
                                    <i class="fas fa-clock" style="opacity: 0.7;"></i>
                                    <span><?php echo esc_html($value); ?></span>
                                <?php else: ?>
                                    <span><?php echo esc_html($value); ?></span>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($content): ?>
                <!-- Hero Content -->
                <div class="hph-hero-content-text <?php echo $fade_in ? 'hph-animate-fade-in-up' : ''; ?>" 
                     style="margin: 0 var(--hph-space-8) var(--hph-space-8) 0; font-size: var(--hph-text-lg); line-height: var(--hph-leading-normal); max-width: 600px; 0.85; <?php echo $fade_in ? 'animation-delay: 0.2s;' : ''; ?>">
                    <?php echo wp_kses_post($content); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($style === 'property' && $listing_id): ?>
                <!-- Property-specific content -->
                <div class="hph-property-hero-details <?php echo $fade_in ? 'hph-animate-fade-in-up' : ''; ?>" 
                     style="margin: 0 0 var(--hph-space-8) 0; <?php echo $fade_in ? 'animation-delay: 0.25s;' : ''; ?>">
                    
                    <?php if ($show_price || $show_status): ?>
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: var(--hph-gap-md); margin-bottom: var(--hph-space-6); <?php echo $alignment === 'center' ? 'justify-content: center;' : ($alignment === 'right' ? 'justify-content: flex-end;' : ''); ?>">
                        <?php if ($show_price && function_exists('hpt_get_listing_price_formatted')): ?>
                            <?php $price = hpt_get_listing_price_formatted($listing_id); ?>
                            <?php if ($price): ?>
                            <div style="font-size: var(--hph-text-3xl); font-weight: var(--hph-font-bold);">
                                <?php echo esc_html($price); ?>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if ($show_status && function_exists('hpt_get_listing_status')): ?>
                            <?php $status = hpt_get_listing_status($listing_id); ?>
                            <?php if ($status): ?>
                            <span style="padding: var(--hph-space-2) var(--hph-space-6); background: rgba(255, 255, 255, 0.9); color: var(--hph-primary); border-radius: var(--hph-radius-full); font-size: var(--hph-text-base); font-weight: var(--hph-font-semibold);">
                                <?php echo esc_html($status); ?>
                            </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($show_stats): ?>
                    <div style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-lg); opacity: 0.9; <?php echo $alignment === 'center' ? 'justify-content: center;' : ($alignment === 'right' ? 'justify-content: flex-end;' : ''); ?>">
                        <?php
                        $bedrooms = function_exists('hpt_get_listing_bedrooms') ? hpt_get_listing_bedrooms($listing_id) : '';
                        $bathrooms = function_exists('hpt_get_listing_bathrooms') ? hpt_get_listing_bathrooms($listing_id) : '';
                        $sqft = function_exists('hpt_get_listing_square_footage') ? hpt_get_listing_square_footage($listing_id) : '';
                        
                        if ($bedrooms): ?>
                        <div style="display: flex; align-items: center; gap: var(--hph-gap-sm);">
                            <svg style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span style="font-weight: var(--hph-font-semibold);"><?php echo esc_html($bedrooms); ?></span>
                            <span><?php echo _n('Bed', 'Beds', intval($bedrooms), 'happy-place-theme'); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($bathrooms): ?>
                        <div style="display: flex; align-items: center; gap: var(--hph-gap-sm);">
                            <svg style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 5h8m-4.5 5.5l.5.5m-1-1v-4a1 1 0 011-1h0a1 1 0 011 1v4m-2 0h2"></path>
                            </svg>
                            <span style="font-weight: var(--hph-font-semibold);"><?php echo esc_html($bathrooms); ?></span>
                            <span><?php echo _n('Bath', 'Baths', floatval($bathrooms), 'happy-place-theme'); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($sqft): ?>
                        <div style="display: flex; align-items: center; gap: var(--hph-gap-sm);">
                            <svg style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                            </svg>
                            <span style="font-weight: var(--hph-font-semibold);"><?php echo esc_html($sqft); ?></span>
                            <span><?php _e('Sq Ft', 'happy-place-theme'); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                </div>
                <?php endif; ?>
                
                <?php if (!empty($buttons)): ?>
                <!-- Hero Buttons -->
                <div class="hph-hero-buttons <?php echo $fade_in ? 'hph-animate-fade-in-up' : ''; ?>" 
                     style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-lg); align-items: center; <?php echo $alignment === 'center' ? 'justify-content: center;' : ($alignment === 'right' ? 'justify-content: flex-end;' : 'justify-content: flex-start;'); ?> <?php echo $fade_in ? 'animation-delay: 0.3s;' : ''; ?>">
                    <?php foreach ($buttons as $index => $button): 
                        $btn_defaults = array(
                            'text' => 'Button',
                            'url' => '#',
                            'style' => 'white',
                            'size' => 'm',
                            'icon' => '',
                            'icon_position' => 'left',
                            'target' => '_self',
                            'data_attributes' => ''
                        );
                        $btn = wp_parse_args($button, $btn_defaults);
                        
                        // Apply theme button styles if set
                        if (!empty($theme_config)) {
                            if ($index === 0 && !empty($theme_config['button_primary'])) {
                                $btn['style'] = $theme_config['button_primary'];
                            } elseif ($index === 1 && !empty($theme_config['button_secondary'])) {
                                $btn['style'] = $theme_config['button_secondary'];
                            }
                        }
                        
                        // Button styles based on style parameter
                        $btn_styles = array(
                            'display: inline-flex',
                            'align-items: center',
                            'justify-content: center',
                            'text-decoration: none',
                            'font-weight: var(--hph-font-semibold)',
                            'border-radius: var(--hph-radius-lg)',
                            'transition: all 300ms ease',
                            'position: relative',
                            'overflow: hidden',
                            'box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)'
                        );
                        
                        // Size-based padding
                        switch($btn['size']) {
                            case 's':
                                $btn_styles[] = 'padding: var(--hph-space-2) var(--hph-space-4)';
                                $btn_styles[] = 'font-size: var(--hph-text-sm)';
                                break;
                            case 'm':
                                $btn_styles[] = 'padding: var(--hph-space-4) var(--hph-space-6)';
                                $btn_styles[] = 'font-size: var(--hph-text-base)';
                                break;
                            case 'l':
                                $btn_styles[] = 'padding: var(--hph-space-4) var(--hph-space-8)';
                                $btn_styles[] = 'font-size: var(--hph-text-base)';
                                break;
                            case 'xl':
                                $btn_styles[] = 'padding: var(--hph-space-6) var(--hph-space-12)';
                                $btn_styles[] = 'font-size: var(--hph-text-lg)';
                                break;
                        }
                        
                        // Style-based colors
                        switch($btn['style']) {
                            case 'white':
                                $btn_styles[] = 'background-color: var(--hph-white)';
                                $btn_styles[] = 'color: var(--hph-primary)';
                                $btn_styles[] = 'border: 2px solid var(--hph-white)';
                                break;
                            case 'outline-white':
                                $btn_styles[] = 'background-color: transparent';
                                $btn_styles[] = 'color: var(--hph-white)';
                                $btn_styles[] = 'border: 2px solid var(--hph-white)';
                                break;
                            case 'primary':
                                $btn_styles[] = 'background-color: var(--hph-primary)';
                                $btn_styles[] = 'color: var(--hph-white)';
                                $btn_styles[] = 'border: 2px solid var(--hph-primary)';
                                break;
                            case 'outline-primary':
                                $btn_styles[] = 'background-color: transparent';
                                $btn_styles[] = 'color: var(--hph-primary)';
                                $btn_styles[] = 'border: 2px solid var(--hph-primary)';
                                break;
                            case 'secondary':
                                $btn_styles[] = 'background-color: var(--hph-secondary)';
                                $btn_styles[] = 'color: var(--hph-white)';
                                $btn_styles[] = 'border: 2px solid var(--hph-secondary)';
                                break;
                            case 'outline-secondary':
                                $btn_styles[] = 'background-color: transparent';
                                $btn_styles[] = 'color: var(--hph-secondary)';
                                $btn_styles[] = 'border: 2px solid var(--hph-secondary)';
                                break;
                            default:
                                $btn_styles[] = 'background-color: var(--hph-primary)';
                                $btn_styles[] = 'color: var(--hph-white)';
                                $btn_styles[] = 'border: 2px solid var(--hph-primary)';
                        }
                        
                        $btn_style_attr = 'style="' . implode('; ', $btn_styles) . '"';
                    ?>
                    <a 
                        href="<?php echo esc_url($btn['url']); ?>"
                        class="hph-hero-btn hph-hero-btn-<?php echo esc_attr($btn['style']); ?><?php echo (!empty($btn['data_attributes']) && strpos($btn['data_attributes'], 'data-modal') !== false && strpos($btn['data_attributes'], 'modal-trigger') === false) ? ' modal-trigger' : ''; ?>"
                        <?php echo $btn_style_attr; ?>
                        <?php if ($btn['target'] !== '_self'): ?>target="<?php echo esc_attr($btn['target']); ?>"<?php endif; ?>
                        <?php if (!empty($btn['data_attributes'])): echo ' ' . $btn['data_attributes']; endif; ?>
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='translateY(0)'"
                    >
                        <?php if ($btn['icon'] && $btn['icon_position'] === 'left'): ?>
                        <i class="<?php echo esc_attr($btn['icon']); ?>" style="margin-right: var(--hph-space-2);"></i>
                        <?php endif; ?>
                        <span><?php echo esc_html($btn['text']); ?></span>
                        <?php if ($btn['icon'] && $btn['icon_position'] === 'right'): ?>
                        <i class="<?php echo esc_attr($btn['icon']); ?>" style="margin-left: var(--hph-space-2);"></i>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    
    <?php if ($scroll_indicator): ?>
    <!-- Scroll Indicator -->
    <div class="hph-hero-scroll" style="position: absolute; bottom: var(--hph-space-6); left: 50%; transform: translateX(-50%); cursor: pointer; transition: opacity 0.3s ease;">
        <div class="hph-scroll-indicator" style="display: flex; flex-direction: column; align-items: center; color: var(--hph-white); opacity: 0.75;">
            <span style="font-size: var(--hph-text-sm); margin-bottom: var(--hph-space-2); font-weight: var(--hph-font-medium);">Scroll</span>
            <div style="width: 2rem; height: 2.5rem; border: 2px solid currentColor; border-radius: var(--hph-radius-full); position: relative;">
                <div class="hph-scroll-dot" style="position: absolute; top: 0.5rem; left: 50%; width: 0.25rem; height: 0.5rem; background: currentColor; border-radius: var(--hph-radius-full); transform: translateX(-50%); animation: bounce 1.5s infinite;"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
</section>

<?php if (!empty($rotating_words)): ?>
<style>
@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideDown {
    from { transform: translateY(-100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(0); opacity: 1; }
    to { transform: translateY(-100%); opacity: 0; }
}

@keyframes flipIn {
    from { transform: rotateX(-90deg); opacity: 0; }
    to { transform: rotateX(0); opacity: 1; }
}

.hph-rotating-text-container {
    vertical-align: baseline;
}

.hph-rotating-text {
    transition: all 0.3s ease;
}

.hph-rotating-text.fade-out {
    opacity: 0;
    transform: translateY(-10px);
}

.hph-rotating-text.fade-in {
    animation: fadeIn 0.5s ease forwards;
}

.hph-rotating-text.slide-out {
    animation: slideUp 0.4s ease forwards;
}

.hph-rotating-text.slide-in {
    animation: slideDown 0.4s ease forwards;
}

.hph-rotating-text.flip-out {
    transform: rotateX(90deg);
    opacity: 0;
    transition: all 0.3s ease;
}

.hph-rotating-text.flip-in {
    animation: flipIn 0.5s ease forwards;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rotatingHeadline = document.querySelector('.hph-rotating-headline');
    if (!rotatingHeadline) return;
    
    const rotatingText = rotatingHeadline.querySelector('.hph-rotating-text');
    if (!rotatingText) return;
    
    const words = JSON.parse(rotatingText.dataset.words || '[]');
    if (words.length <= 1) return;
    
    const rotationType = rotatingHeadline.dataset.rotationType || 'typing';
    const rotationSpeed = parseInt(rotatingHeadline.dataset.rotationSpeed) || 3000;
    const typingSpeed = parseInt(rotatingHeadline.dataset.typingSpeed) || 100;
    const cursor = rotatingHeadline.querySelector('.hph-typing-cursor');
    
    let currentIndex = 0;
    
    function typeWriter(text, element, callback) {
        let i = 0;
        element.textContent = '';
        
        function type() {
            if (i < text.length) {
                element.textContent += text.charAt(i);
                i++;
                setTimeout(type, typingSpeed);
            } else if (callback) {
                setTimeout(callback, rotationSpeed);
            }
        }
        type();
    }
    
    function deleteWriter(element, callback) {
        let text = element.textContent;
        let i = text.length;
        
        function deleteChar() {
            if (i > 0) {
                element.textContent = text.substring(0, i - 1);
                i--;
                setTimeout(deleteChar, typingSpeed / 2);
            } else if (callback) {
                callback();
            }
        }
        deleteChar();
    }
    
    function rotateWord() {
        currentIndex = (currentIndex + 1) % words.length;
        const nextWord = words[currentIndex];
        
        switch(rotationType) {
            case 'typing':
                if (cursor) cursor.style.display = 'inline-block';
                deleteWriter(rotatingText, function() {
                    typeWriter(nextWord, rotatingText, rotateWord);
                });
                break;
                
            case 'fade':
                rotatingText.classList.add('fade-out');
                setTimeout(() => {
                    rotatingText.textContent = nextWord;
                    rotatingText.classList.remove('fade-out');
                    rotatingText.classList.add('fade-in');
                    setTimeout(() => {
                        rotatingText.classList.remove('fade-in');
                        setTimeout(rotateWord, rotationSpeed);
                    }, 500);
                }, 300);
                break;
                
            case 'slide':
                rotatingText.classList.add('slide-out');
                setTimeout(() => {
                    rotatingText.textContent = nextWord;
                    rotatingText.classList.remove('slide-out');
                    rotatingText.classList.add('slide-in');
                    setTimeout(() => {
                        rotatingText.classList.remove('slide-in');
                        setTimeout(rotateWord, rotationSpeed);
                    }, 400);
                }, 400);
                break;
                
            case 'flip':
                rotatingText.classList.add('flip-out');
                setTimeout(() => {
                    rotatingText.textContent = nextWord;
                    rotatingText.classList.remove('flip-out');
                    rotatingText.classList.add('flip-in');
                    setTimeout(() => {
                        rotatingText.classList.remove('flip-in');
                        setTimeout(rotateWord, rotationSpeed);
                    }, 500);
                }, 300);
                break;
                
            default:
                rotatingText.textContent = nextWord;
                setTimeout(rotateWord, rotationSpeed);
        }
    }
    
    // Start rotation
    if (rotationType === 'typing') {
        setTimeout(rotateWord, rotationSpeed);
    } else {
        setTimeout(rotateWord, rotationSpeed);
    }
});
</script>
<?php endif; ?>

<?php if ($background_image): ?>
<!-- Smooth Image Loading Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const heroSection = document.querySelector('<?php echo $section_id ? "#" . esc_js($section_id) : ".hph-hero.hph-hero-image"; ?>');
    
    if (heroSection && heroSection.style.backgroundImage) {
        // Create a temporary image to preload
        const img = new Image();
        const bgUrl = '<?php echo esc_js($background_image); ?>';
        
        // Set loading state
        heroSection.classList.add('hph-hero-loading');
        
        img.onload = function() {
            // Image loaded successfully, fade out overlay color
            heroSection.classList.add('hph-hero-loaded');
            heroSection.classList.remove('hph-hero-loading');
        };
        
        img.onerror = function() {
            // Image failed to load, keep overlay color
            heroSection.classList.remove('hph-hero-loading');
        };
        
        // Start loading the image
        img.src = bgUrl;
    }
});
</script>

<style>
.hph-hero-loading {
    /* Keep the solid background color visible while loading */
    position: relative;
}

.hph-hero-loading::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: inherit;
    z-index: 0;
    opacity: 1;
    transition: opacity 0.8s ease-out;
}

.hph-hero-loaded::before {
    /* Fade out the overlay to reveal the image */
    opacity: 0;
}

/* Smooth transition for the background color fade */
.hph-hero[data-hero-style="image"] {
    transition: background-color 0.8s ease-out;
}
</style>
<?php endif; ?>

<!-- Enhanced Blur and Animation Effects -->
<style>
/* Content Animation Keyframes */
@keyframes hph-fade-up {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes hph-slide-up {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes hph-zoom-in {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes hph-bounce-in {
    0% {
        opacity: 0;
        transform: scale(0.8) translateY(30px);
    }
    60% {
        opacity: 1;
        transform: scale(1.05) translateY(0);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes hph-float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes hph-float-slow {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-15px) rotate(1deg);
    }
}

@keyframes hph-float-delayed {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

/* Animation Classes */
.hph-animate-fade-up {
    animation: hph-fade-up 0.8s ease-out forwards;
    opacity: 0;
}

.hph-animate-slide-up {
    animation: hph-slide-up 0.8s ease-out forwards;
    opacity: 0;
}

.hph-animate-zoom-in {
    animation: hph-zoom-in 0.8s ease-out forwards;
    opacity: 0;
}

.hph-animate-bounce-in {
    animation: hph-bounce-in 1s ease-out forwards;
    opacity: 0;
}

/* Ken Burns Effect Animations */
@keyframes kenBurnszoomin {
    0% {
        transform: scale(1) translate(0, 0);
    }
    100% {
        transform: scale(1.1) translate(-2%, -1%);
    }
}

@keyframes kenBurnszoomout {
    0% {
        transform: scale(1.1) translate(-2%, -1%);
    }
    100% {
        transform: scale(1) translate(0, 0);
    }
}

@keyframes kenBurnspanleft {
    0% {
        transform: scale(1.05) translate(0, 0);
    }
    100% {
        transform: scale(1.05) translate(-4%, 0);
    }
}

@keyframes kenBurnspanright {
    0% {
        transform: scale(1.05) translate(-4%, 0);
    }
    100% {
        transform: scale(1.05) translate(0, 0);
    }
}

@keyframes kenBurnszoomPan {
    0% {
        transform: scale(1) translate(0, 0);
    }
    50% {
        transform: scale(1.08) translate(-3%, -2%);
    }
    100% {
        transform: scale(1.05) translate(2%, 1%);
    }
}

/* Parallax Intensity Effects */
.hph-hero-parallax.parallax-subtle {
    transform: translateY(0);
    transition: transform 0.5s ease-out;
}

.hph-hero-parallax.parallax-normal {
    transform: translateY(0);
    transition: transform 0.3s ease-out;
}

.hph-hero-parallax.parallax-strong {
    transform: translateY(0);
    transition: transform 0.1s ease-out;
}

/* Enhanced Backdrop Blur Support */
@supports (backdrop-filter: blur(1px)) {
    .hph-hero-overlay {
        backdrop-filter: inherit;
    }
}

@supports not (backdrop-filter: blur(1px)) {
    .hph-hero-overlay {
        background: rgba(0, 0, 0, 0.3) !important;
    }
}

/* Smooth scroll indicator animation */
.hph-hero-scroll:hover .hph-scroll-indicator {
    opacity: 1;
    transform: translateY(-5px);
    transition: all 0.3s ease;
}

/* Ken Burns Performance Optimization */
.hph-ken-burns-image {
    will-change: transform;
    backface-visibility: hidden;
    perspective: 1000px;
}

/* Media Queries for Mobile Optimization */
@media (max-width: 768px) {
    .hph-ken-burns-image {
        animation-duration: 30s; /* Slower on mobile for better performance */
    }
}

/* Reduced motion preferences */
@media (prefers-reduced-motion: reduce) {
    .hph-animate-fade-up,
    .hph-animate-slide-up,
    .hph-animate-zoom-in,
    .hph-animate-bounce-in {
        animation: none;
        opacity: 1;
    }

    .hph-ken-burns-image {
        animation: none;
    }
}
</style>

<!-- Enhanced Parallax Script -->
<?php if ($parallax && $parallax_intensity !== 'normal'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const heroSection = document.querySelector('<?php echo $section_id ? "#" . esc_js($section_id) : ".hph-hero"; ?>');
    if (!heroSection) return;

    const intensity = '<?php echo esc_js($parallax_intensity); ?>';
    let multiplier = 0.5;

    switch(intensity) {
        case 'subtle':
            multiplier = 0.2;
            break;
        case 'strong':
            multiplier = 0.8;
            break;
        default:
            multiplier = 0.5;
    }

    function updateParallax() {
        const scrolled = window.pageYOffset;
        const offset = scrolled * multiplier;
        heroSection.style.transform = `translateY(${offset}px)`;
    }

    // Throttled scroll event
    let ticking = false;
    function requestTick() {
        if (!ticking) {
            requestAnimationFrame(updateParallax);
            ticking = true;
            setTimeout(() => { ticking = false; }, 16);
        }
    }

    window.addEventListener('scroll', requestTick);

    // Initial call
    updateParallax();
});
</script>
<?php endif; ?>
