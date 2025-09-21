<?php
/**
 * HPH Features Section Template
 * Rewritten using only CSS variables and inline styles
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/features');
}

// Default arguments
$defaults = array(
    'layout' => 'grid',
    'background' => 'white',
    'padding' => 'xl',
    'content_width' => 'normal',
    'alignment' => 'center',
    'columns' => 3,
    'badge' => '',
    'headline' => 'Our Features',
    'subheadline' => '',
    'content' => '',
    'features' => array(),
    'icon_style' => 'default',
    'icon_position' => 'top',
    'animation' => false,
    'section_id' => ''
);

// Merge with provided args - use consistent null coalescing
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Build section styles
$section_styles = array(
    'position: relative',
    'width: 100%'
);

// Background styles
switch ($background) {
    case 'light':
        $section_styles[] = 'background-color: var(--hph-gray-50)';
        $section_styles[] = 'color: var(--hph-text-color)';
        break;
    case 'dark':
        $section_styles[] = 'background-color: var(--hph-gray-900)';
        $section_styles[] = 'color: var(--hph-white)';
        break;
    case 'primary':
        $section_styles[] = 'background-color: var(--hph-primary)';
        $section_styles[] = 'color: var(--hph-white)';
        break;
    case 'gradient':
        $section_styles[] = 'background: var(--hph-gradient-primary)';
        $section_styles[] = 'color: var(--hph-white)';
        break;
    case 'white':
    default:
        $section_styles[] = 'background-color: var(--hph-white)';
        $section_styles[] = 'color: var(--hph-text-color)';
        break;
}

// Padding styles
switch ($padding) {
    case 'sm':
        $section_styles[] = 'padding-top: var(--hph-padding-lg)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-lg)';
        break;
    case 'md':
        $section_styles[] = 'padding-top: var(--hph-padding-xl)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-xl)';
        break;
    case 'lg':
        $section_styles[] = 'padding-top: var(--hph-padding-2xl)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-2xl)';
        break;
    case '2xl':
        $section_styles[] = 'padding-top: var(--hph-padding-4xl)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-4xl)';
        break;
    case 'xl':
    default:
        $section_styles[] = 'padding-top: var(--hph-padding-3xl)';
        $section_styles[] = 'padding-bottom: var(--hph-padding-3xl)';
        break;
}

// Container styles
$container_styles = array(
    'position: relative',
    'margin-left: auto',
    'margin-right: auto',
    'padding-left: var(--hph-padding-lg)',
    'padding-right: var(--hph-padding-lg)'
);

// Content width
switch ($content_width) {
    case 'narrow':
        $container_styles[] = 'max-width: var(--hph-container-sm)';
        break;
    case 'wide':
        $container_styles[] = 'max-width: var(--hph-container-2xl)';
        break;
    case 'full':
        $container_styles[] = 'max-width: 100%';
        $container_styles[] = 'padding-left: 0';
        $container_styles[] = 'padding-right: 0';
        break;
    case 'normal':
    default:
        $container_styles[] = 'max-width: var(--hph-container-xl)';
        break;
}

// Text alignment for header
$header_alignment = '';
switch ($alignment) {
    case 'left':
        $header_alignment = 'text-align: left';
        break;
    case 'right':
        $header_alignment = 'text-align: right';
        break;
    case 'center':
    default:
        $header_alignment = 'text-align: center';
        break;
}

// Build grid styles based on layout and columns
$grid_styles = array();

if ($layout === 'grid' || $layout === 'cards') {
    $grid_styles[] = 'display: grid';
    $grid_styles[] = 'gap: var(--hph-gap-xl)';
    
    switch ($columns) {
        case 2:
            $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(300px, 1fr))';
            break;
        case 4:
            $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(250px, 1fr))';
            break;
        case 3:
        default:
            $grid_styles[] = 'grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))';
            break;
    }
} elseif ($layout === 'list') {
    $grid_styles[] = 'display: flex';
    $grid_styles[] = 'flex-direction: column';
    $grid_styles[] = 'gap: var(--hph-gap-xl)';
} elseif ($layout === 'alternating') {
    $grid_styles[] = 'display: flex';
    $grid_styles[] = 'flex-direction: column';
    $grid_styles[] = 'gap: var(--hph-gap-3xl)';
} elseif ($layout === 'timeline') {
    $grid_styles[] = 'position: relative';
    $grid_styles[] = 'display: flex';
    $grid_styles[] = 'flex-direction: column';
    $grid_styles[] = 'gap: var(--hph-gap-2xl)';
}

// Icon wrapper styles based on icon_style
if (!function_exists('getIconStyles')) {
function getIconStyles($icon_style) {
    $styles = array(
        'display: inline-flex',
        'align-items: center',
        'justify-content: center'
    );
    
    switch ($icon_style) {
        case 'circle':
            $styles[] = 'width: 4rem';
            $styles[] = 'height: 4rem';
            $styles[] = 'border-radius: var(--hph-radius-full)';
            $styles[] = 'background-color: var(--hph-primary-100)';
            break;
        case 'square':
            $styles[] = 'width: 4rem';
            $styles[] = 'height: 4rem';
            $styles[] = 'border-radius: var(--hph-radius-lg)';
            $styles[] = 'background-color: var(--hph-primary-100)';
            break;
        case 'gradient':
            $styles[] = 'width: 4rem';
            $styles[] = 'height: 4rem';
            $styles[] = 'border-radius: var(--hph-radius-full)';
            $styles[] = 'background: var(--hph-gradient-primary)';
            $styles[] = 'color: var(--hph-white)';
            break;
        case 'outline':
            $styles[] = 'width: 4rem';
            $styles[] = 'height: 4rem';
            $styles[] = 'border-radius: var(--hph-radius-full)';
            $styles[] = 'border: 2px solid var(--hph-primary)';
            break;
    }
    
    return implode('; ', $styles);
}
}
?>

<section 
    class="hph-features-section"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    data-bg="<?php echo esc_attr($background); ?>"
    style="<?php echo implode('; ', $section_styles); ?>"
    data-animation="<?php echo $animation ? 'true' : 'false'; ?>"
>
    <div style="<?php echo implode('; ', $container_styles); ?>">
        
        <?php if ($badge || $headline || $subheadline || $content): ?>
        <!-- Section Header -->
        <div style="margin-bottom: var(--hph-margin-3xl); <?php echo $header_alignment; ?> <?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
            
            <?php if ($badge): ?>
            <!-- Badge -->
            <div style="margin-bottom: var(--hph-margin-lg);">
                <span style="display: inline-block; padding: var(--hph-padding-sm) var(--hph-padding-md); background: var(--hph-primary-100); color: var(--hph-primary-700); border-radius: var(--hph-radius-full); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold);">
                    <?php echo esc_html($badge); ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if ($headline): ?>
            <!-- Headline -->
            <h2 style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-4xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
                <?php echo esc_html($headline); ?>
            </h2>
            <?php endif; ?>
            
            <?php if ($subheadline): ?>
            <!-- Subheadline -->
            <p style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-medium); opacity: 0.9;">
                <?php echo esc_html($subheadline); ?>
            </p>
            <?php endif; ?>
            
            <?php if ($content): ?>
            <!-- Content -->
            <div style="font-size: var(--hph-text-base); line-height: var(--hph-leading-relaxed); max-width: 65ch; margin-left: auto; margin-right: auto;">
                <?php echo wp_kses_post($content); ?>
            </div>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        
        <?php if (!empty($features)): ?>
        <!-- Features List -->
        <div style="<?php echo implode('; ', $grid_styles); ?>">
            
            <?php if ($layout === 'timeline'): ?>
            <!-- Timeline Line -->
            <div style="position: absolute; left: 2rem; top: 0; bottom: 0; width: 1px; background-color: var(--hph-gray-200);"></div>
            <?php endif; ?>
            
            <?php foreach ($features as $index => $feature): 
                $feature_defaults = array(
                    'icon' => '',
                    'title' => '',
                    'content' => '',
                    'description' => '', // Alias for content
                    'button' => null,
                    'link' => null,
                    'image' => null
                );
                $feature = wp_parse_args($feature, $feature_defaults);
                
                // Handle 'description' as alias for 'content'
                if (!empty($feature['description']) && empty($feature['content'])) {
                    $feature['content'] = $feature['description'];
                }
                
                // Build feature item styles
                $item_styles = array();
                
                if ($layout === 'cards') {
                    $item_styles[] = 'background: var(--hph-white)';
                    $item_styles[] = 'border-radius: var(--hph-radius-lg)';
                    $item_styles[] = 'box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1)';
                    $item_styles[] = 'padding: var(--hph-padding-xl)';
                    $item_styles[] = 'transition: all 300ms ease';
                } elseif ($layout === 'list') {
                    if ($icon_position === 'left') {
                        $item_styles[] = 'display: flex';
                        $item_styles[] = 'align-items: flex-start';
                        $item_styles[] = 'gap: var(--hph-gap-xl)';
                    }
                } elseif ($layout === 'alternating') {
                    $item_styles[] = 'display: grid';
                    $item_styles[] = 'grid-template-columns: 1fr';
                    $item_styles[] = 'gap: var(--hph-gap-2xl)';
                    $item_styles[] = 'align-items: center';
                    
                    // Add responsive grid for larger screens
                    if ($index % 2 !== 0) {
                        $item_styles[] = 'direction: rtl';
                    }
                } elseif ($layout === 'timeline') {
                    $item_styles[] = 'position: relative';
                    $item_styles[] = 'padding-left: 5rem';
                }
                
                $animation_delay = $animation ? 'animation: fadeInUp 0.8s ease-out ' . ($index * 0.15) . 's; opacity: 0; animation-fill-mode: forwards;' : '';
            ?>
            
            <div 
                style="<?php echo implode('; ', $item_styles); ?> <?php echo $animation_delay; ?>"
                <?php if ($layout === 'cards'): ?>
                onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 15px rgba(0, 0, 0, 0.15)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)';"
                <?php endif; ?>
            >
                
                <?php if ($layout === 'timeline'): ?>
                <!-- Timeline Dot -->
                <div style="position: absolute; left: 1.5rem; top: 0.5rem; width: 1rem; height: 1rem; background-color: var(--hph-primary); border-radius: var(--hph-radius-full); border: 4px solid var(--hph-white); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);"></div>
                <?php endif; ?>
                
                <?php if ($layout === 'alternating' && isset($feature['image']) && !empty($feature['image']) && !empty($feature['image']['url'])): ?>
                <!-- Alternating Layout - Image -->
                <div style="<?php echo $index % 2 !== 0 ? 'direction: ltr;' : ''; ?>">
                    <div class="hph-feature-image-container" style="position: relative; overflow: hidden; border-radius: var(--hph-radius-lg);">
                        <img 
                            src="<?php echo esc_url($feature['image']['url']); ?>" 
                            alt="<?php echo esc_attr($feature['image']['alt'] ?? $feature['title']); ?>"
                            class="hph-feature-image"
                            style="width: 100%; height: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);"
                            loading="lazy"
                        >
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Feature Content -->
                <div style="<?php echo $layout === 'list' && $icon_position === 'left' ? 'flex: 1;' : ''; ?> <?php echo ($layout === 'alternating' && $index % 2 !== 0) ? 'direction: ltr;' : ''; ?>">
                    
                    <?php if (!empty($feature['icon']) && $icon_position !== 'inline'): ?>
                    <!-- Icon -->
                    <div style="<?php echo $icon_position === 'top' ? 'margin-bottom: var(--hph-margin-lg);' : ''; ?>">
                        <?php if ($icon_style === 'default'): ?>
                        <i class="<?php echo esc_attr($feature['icon']); ?>" style="font-size: var(--hph-text-4xl); color: var(--hph-primary);"></i>
                        <?php else: ?>
                        <div style="<?php echo getIconStyles($icon_style); ?>">
                            <i class="<?php echo esc_attr($feature['icon']); ?>" style="font-size: var(--hph-text-2xl);"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($feature['title'])): ?>
                    <!-- Title -->
                    <h3 style="margin: 0 0 var(--hph-margin-md) 0; font-size: var(--hph-text-xl); font-weight: var(--hph-font-semibold); line-height: var(--hph-leading-tight);">
                        <?php if (!empty($feature['icon']) && $icon_position === 'inline'): ?>
                        <i class="<?php echo esc_attr($feature['icon']); ?>" style="font-size: var(--hph-text-lg); color: var(--hph-primary); margin-right: var(--hph-margin-sm);"></i>
                        <?php endif; ?>
                        <?php echo esc_html($feature['title']); ?>
                    </h3>
                    <?php endif; ?>
                    
                    <?php if (!empty($feature['content'])): ?>
                    <!-- Content -->
                    <p style="color: var(--hph-gray-600); line-height: var(--hph-leading-relaxed); margin-bottom: var(--hph-margin-lg);">
                        <?php echo wp_kses_post($feature['content']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($feature['button'])): ?>
                    <!-- Button -->
                    <div style="margin-bottom: var(--hph-margin-md);">
                        <?php
                        $button = wp_parse_args($feature['button'], array(
                            'text' => 'Learn More',
                            'url' => '#',
                            'style' => 'primary',
                            'size' => 'md',
                            'icon' => '',
                            'target' => '_self'
                        ));
                        
                        // Button style classes
                        $button_styles = array(
                            'display: inline-flex',
                            'align-items: center',
                            'gap: var(--hph-gap-sm)',
                            'text-decoration: none',
                            'border-radius: var(--hph-radius-md)',
                            'font-weight: var(--hph-font-medium)',
                            'transition: all 0.3s ease',
                            'border: 2px solid transparent'
                        );
                        
                        // Size styles
                        switch ($button['size']) {
                            case 'sm':
                                $button_styles[] = 'padding: var(--hph-padding-sm) var(--hph-padding-md)';
                                $button_styles[] = 'font-size: var(--hph-text-sm)';
                                break;
                            case 'lg':
                                $button_styles[] = 'padding: var(--hph-padding-lg) var(--hph-padding-xl)';
                                $button_styles[] = 'font-size: var(--hph-text-lg)';
                                break;
                            case 'md':
                            default:
                                $button_styles[] = 'padding: var(--hph-padding-md) var(--hph-padding-lg)';
                                $button_styles[] = 'font-size: var(--hph-text-base)';
                                break;
                        }
                        
                        // Style variations
                        switch ($button['style']) {
                            case 'secondary':
                                $button_styles[] = 'background-color: var(--hph-secondary)';
                                $button_styles[] = 'color: var(--hph-white)';
                                $hover_bg = 'var(--hph-secondary-dark)';
                                break;
                            case 'outline':
                                $button_styles[] = 'background-color: transparent';
                                $button_styles[] = 'color: var(--hph-primary)';
                                $button_styles[] = 'border-color: var(--hph-primary)';
                                $hover_bg = 'var(--hph-primary)';
                                $hover_color = 'var(--hph-white)';
                                break;
                            case 'outline-secondary':
                                $button_styles[] = 'background-color: transparent';
                                $button_styles[] = 'color: var(--hph-secondary)';
                                $button_styles[] = 'border-color: var(--hph-secondary)';
                                $hover_bg = 'var(--hph-secondary)';
                                $hover_color = 'var(--hph-white)';
                                break;
                            case 'text':
                                $button_styles[] = 'background-color: transparent';
                                $button_styles[] = 'color: var(--hph-primary)';
                                $button_styles[] = 'padding: var(--hph-padding-sm) 0';
                                $hover_color = 'var(--hph-primary-dark)';
                                break;
                            case 'primary':
                            default:
                                $button_styles[] = 'background-color: var(--hph-primary)';
                                $button_styles[] = 'color: var(--hph-white)';
                                $hover_bg = 'var(--hph-primary-dark)';
                                break;
                        }
                        ?>
                        <a 
                            href="<?php echo esc_url($button['url']); ?>" 
                            style="<?php echo implode('; ', $button_styles); ?>"
                            <?php if ($button['target'] !== '_self'): ?>target="<?php echo esc_attr($button['target']); ?>"<?php endif; ?>
                            onmouseover="
                                <?php if (isset($hover_bg)): ?>this.style.backgroundColor='<?php echo $hover_bg; ?>';<?php endif; ?>
                                <?php if (isset($hover_color)): ?>this.style.color='<?php echo $hover_color; ?>';<?php endif; ?>
                                this.style.transform='translateY(-1px)';
                                this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';
                            "
                            onmouseout="
                                <?php if ($button['style'] === 'outline' || $button['style'] === 'outline-secondary'): ?>
                                    this.style.backgroundColor='transparent';
                                    this.style.color='<?php echo $button['style'] === 'outline' ? 'var(--hph-primary)' : 'var(--hph-secondary)'; ?>';
                                <?php elseif ($button['style'] === 'text'): ?>
                                    this.style.color='var(--hph-primary)';
                                <?php else: ?>
                                    this.style.backgroundColor='<?php echo $button['style'] === 'secondary' ? 'var(--hph-secondary)' : 'var(--hph-primary)'; ?>';
                                    this.style.color='var(--hph-white)';
                                <?php endif; ?>
                                this.style.transform='translateY(0)';
                                this.style.boxShadow='none';
                            "
                        >
                            <?php if (!empty($button['icon'])): ?>
                            <i class="<?php echo esc_attr($button['icon']); ?>"></i>
                            <?php endif; ?>
                            <?php echo esc_html($button['text']); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($feature['link']) && !empty($feature['link']['url'])): ?>
                    <!-- Link -->
                    <a 
                        href="<?php echo esc_url($feature['link']['url']); ?>" 
                        style="display: inline-flex; align-items: center; color: var(--hph-primary); font-weight: var(--hph-font-medium); text-decoration: none;"
                        onmouseover="this.style.textDecoration='underline'"
                        onmouseout="this.style.textDecoration='none'"
                        <?php if (!empty($feature['link']['target'])): ?>target="<?php echo esc_attr($feature['link']['target']); ?>"<?php endif; ?>
                    >
                        <?php echo esc_html($feature['link']['text'] ?? 'Learn More'); ?>
                        <i class="fas fa-arrow-right" style="margin-left: var(--hph-margin-sm); font-size: var(--hph-text-sm);"></i>
                    </a>
                    <?php endif; ?>
                    
                </div>
                
            </div>
            
            <?php endforeach; ?>
            
        </div>
        <?php endif; ?>
        
    </div>
</section>

<?php if ($animation): ?>
<style>
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

/* Responsive adjustments for alternating layout */
@media (min-width: 1024px) {
    .hph-features-section [style*="grid-template-columns: 1fr"] {
        grid-template-columns: 1fr 1fr !important;
    }
}
</style>
<?php endif; ?>

<style>
/* HPH Features - Duotone Effects for Images */
.hph-features-section .hph-feature-image-container {
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.hph-features-section .hph-feature-image {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Simple blue overlay with minimal opacity for alternating layout images */
.hph-features-section .hph-feature-image-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--hph-primary);
    opacity: 0.15;
    transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1;
}

/* Hover Effects for alternating layout - Fade away the blue overlay */
.hph-features-section .hph-feature-image-container:hover {
    transform: scale(1.02);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2) !important;
}

.hph-features-section .hph-feature-image-container:hover::before {
    opacity: 0;
}

.hph-features-section .hph-feature-image-container:hover .hph-feature-image {
    transform: scale(1.03);
}

/* Focus states for accessibility */
.hph-features-section .hph-feature-image-container:focus-within {
    outline: 2px solid var(--hph-primary);
    outline-offset: 2px;
    transform: scale(1.02);
}

/* Reduced motion for accessibility */
@media (prefers-reduced-motion: reduce) {
    .hph-features-section .hph-feature-image-container,
    .hph-features-section .hph-feature-image,
    .hph-features-section .hph-feature-image-container::before {
        transition: none;
    }
    
    .hph-features-section .hph-feature-image-container:hover {
        transform: none;
    }
    
    .hph-features-section .hph-feature-image-container:hover .hph-feature-image {
        transform: none;
    }
}
</style>
