<?php
/**
 * HPH CTA Section Template
 * Rewritten using only CSS variables and inline styles
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Register this template part for asset loading
if (function_exists('hph_register_template_part')) {
    hph_register_template_part('sections/cta');
}

// Default arguments
$defaults = array(
    'layout' => 'centered',
    'background' => 'primary',
    'background_image' => '',
    'overlay' => true,
    'overlay_opacity' => '40',
    'padding' => 'xl',
    'content_width' => 'normal',
    'alignment' => 'center',
    'badge' => '',
    'headline' => 'Ready to Get Started?',
    'subheadline' => '',
    'content' => '',
    'buttons' => array(
        array(
            'text' => 'Get Started',
            'url' => '#',
            'style' => 'white',
            'size' => 'xl'
        )
    ),
    'image' => null,
    'form' => null,
    'animation' => false,
    'section_id' => ''
);

// Merge with provided args
$config = wp_parse_args($args ?? array(), $defaults);
extract($config);

// Build section styles
$section_styles = array(
    'position: relative',
    'width: 100%',
    'overflow: hidden'
);

// Background styles
if ($background === 'image' && $background_image) {
    $section_styles[] = "background-image: url('" . esc_url($background_image) . "')";
    $section_styles[] = 'background-size: cover';
    $section_styles[] = 'background-position: center';
    $section_styles[] = 'background-repeat: no-repeat';
    $section_styles[] = 'color: var(--hph-white)';
} else {
    switch ($background) {
        case 'secondary':
            $section_styles[] = 'background-color: var(--hph-secondary)';
            $section_styles[] = 'color: var(--hph-white)';
            break;
        case 'gradient':
            $section_styles[] = 'background: var(--hph-gradient-primary)';
            $section_styles[] = 'color: var(--hph-white)';
            break;
        case 'dark':
            $section_styles[] = 'background-color: var(--hph-gray-900)';
            $section_styles[] = 'color: var(--hph-white)';
            break;
        case 'light':
            $section_styles[] = 'background-color: var(--hph-gray-50)';
            $section_styles[] = 'color: var(--hph-gray-900)';
            break;
        case 'primary':
        default:
            $section_styles[] = 'background-color: var(--hph-primary)';
            $section_styles[] = 'color: var(--hph-white)';
            break;
    }
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
    'z-index: 10',
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

// Layout-specific container styles
if ($layout === 'split' || $layout === 'inline') {
    $container_styles[] = 'display: grid';
    $container_styles[] = 'grid-template-columns: 1fr';
    $container_styles[] = 'gap: var(--hph-gap-2xl)';
    $container_styles[] = 'align-items: center';
}

if ($layout === 'boxed') {
    $inner_box_styles = array(
        'background-color: var(--hph-white)',
        'border-radius: var(--hph-radius-xl)',
        'box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25)',
        'padding: var(--hph-padding-3xl)',
        'color: var(--hph-gray-900)'
    );
}

// Text alignment
switch ($alignment) {
    case 'left':
        $container_styles[] = 'text-align: left';
        break;
    case 'right':
        $container_styles[] = 'text-align: right';
        break;
    case 'center':
    default:
        $container_styles[] = 'text-align: center';
        break;
}

// Build overlay styles
$overlay_styles = array();
if ($background === 'image' && $background_image && $overlay) {
    $overlay_styles[] = 'position: absolute';
    $overlay_styles[] = 'top: 0';
    $overlay_styles[] = 'left: 0';
    $overlay_styles[] = 'right: 0';
    $overlay_styles[] = 'bottom: 0';
    $overlay_styles[] = 'background-color: rgba(0, 0, 0, 0.' . $overlay_opacity . ')';
    $overlay_styles[] = 'z-index: 1';
}

// Button justify styles
$button_justify = '';
switch ($alignment) {
    case 'left':
        $button_justify = 'justify-content: flex-start';
        break;
    case 'right':
        $button_justify = 'justify-content: flex-end';
        break;
    case 'center':
    default:
        $button_justify = 'justify-content: center';
        break;
}

// Media query for larger screens
$responsive_style = '';
if ($layout === 'split' || $layout === 'inline') {
    $unique_id = uniqid();
    $responsive_style = '@media (min-width: 1024px) { .cta-container-' . $unique_id . ' { grid-template-columns: 1fr 1fr; } }';
}
?>

<section 
    class="hph-cta-section"
    <?php if ($section_id): ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
    data-bg="<?php echo esc_attr($background); ?>"
    style="<?php echo implode('; ', $section_styles); ?>"
    data-animation="<?php echo $animation ? 'true' : 'false'; ?>"
>
    <?php if ($responsive_style): ?>
    <style><?php echo $responsive_style; ?></style>
    <?php endif; ?>
    
    <?php if ($background === 'image' && $background_image && $overlay): ?>
    <!-- Overlay -->
    <div style="<?php echo implode('; ', $overlay_styles); ?>"></div>
    <?php endif; ?>
    
    <div class="cta-container-<?php echo isset($unique_id) ? $unique_id : uniqid(); ?>" style="<?php echo implode('; ', $container_styles); ?>">
        
        <?php if ($layout === 'boxed'): ?>
        <div style="<?php echo implode('; ', $inner_box_styles); ?>">
        <?php endif; ?>
        
        <?php if ($layout === 'split' || $layout === 'inline'): ?>
        
        <!-- Split/Inline Layout -->
        <div style="<?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
            
            <?php if ($badge): ?>
            <!-- Badge -->
            <div style="margin-bottom: var(--hph-margin-lg);">
                <span style="display: inline-block; padding: var(--hph-padding-sm) var(--hph-padding-md); background: rgba(255, 255, 255, 0.2); color: currentColor; backdrop-filter: blur(10px); border-radius: var(--hph-radius-full); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold);">
                    <?php echo esc_html($badge); ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if ($headline): ?>
            <!-- Headline -->
            <h2 style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-5xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
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
            <div style="margin: 0 0 var(--hph-margin-2xl) 0; font-size: var(--hph-text-base); line-height: var(--hph-leading-relaxed); opacity: 0.85;">
                <?php echo wp_kses_post($content); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($layout === 'inline' && !empty($buttons)): ?>
            <!-- Inline Buttons -->
            <div style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-lg); align-items: center; <?php echo $button_justify; ?>">
                <?php foreach ($buttons as $button): 
                    $btn = wp_parse_args($button, array(
                        'text' => 'Button',
                        'url' => '#',
                        'style' => 'white',
                        'size' => 'xl',
                        'icon' => '',
                        'target' => '_self'
                    ));
                    
                    $btn_styles = array(
                        'display: inline-flex',
                        'align-items: center',
                        'justify-content: center',
                        'text-decoration: none',
                        'font-weight: var(--hph-font-semibold)',
                        'border-radius: var(--hph-radius-lg)',
                        'transition: all 300ms ease',
                        'box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1)'
                    );
                    
                    // Button size
                    switch($btn['size']) {
                        case 'xl':
                            $btn_styles[] = 'padding: var(--hph-padding-lg) var(--hph-padding-2xl)';
                            $btn_styles[] = 'font-size: var(--hph-text-lg)';
                            break;
                        default:
                            $btn_styles[] = 'padding: var(--hph-padding-md) var(--hph-padding-xl)';
                            $btn_styles[] = 'font-size: var(--hph-text-base)';
                    }
                    
                    // Button style
                    switch($btn['style']) {
                        case 'outline-white':
                            $btn_styles[] = 'background-color: transparent';
                            $btn_styles[] = 'color: var(--hph-white)';
                            $btn_styles[] = 'border: 2px solid var(--hph-white)';
                            break;
                        case 'white':
                        default:
                            $btn_styles[] = 'background-color: var(--hph-white)';
                            $btn_styles[] = 'color: var(--hph-primary)';
                            $btn_styles[] = 'border: 2px solid var(--hph-white)';
                    }
                ?>
                <a 
                    href="<?php echo esc_url($btn['url']); ?>"
                    style="<?php echo implode('; ', $btn_styles); ?>"
                    <?php if ($btn['target'] !== '_self'): ?>target="<?php echo esc_attr($btn['target']); ?>"<?php endif; ?>
                    onmouseover="this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.transform='translateY(0)'"
                >
                    <?php if ($btn['icon']): ?>
                    <i class="<?php echo esc_attr($btn['icon']); ?>" style="margin-right: var(--hph-margin-sm);"></i>
                    <?php endif; ?>
                    <span><?php echo esc_html($btn['text']); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
        </div>
        
        <?php if ($layout === 'split'): ?>
        <!-- Split Layout - Image or Form -->
        <div style="<?php echo $animation ? 'animation: fadeInUp 0.8s ease-out 0.2s; opacity: 0; animation-fill-mode: forwards;' : ''; ?>">
            
            <?php if ($form): ?>
            <!-- Form -->
            <div style="background: var(--hph-white); border-radius: var(--hph-radius-lg); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); padding: var(--hph-padding-xl);">
                <form style="display: flex; flex-direction: column; gap: var(--hph-gap-lg);">
                    <?php if (!empty($form['title'])): ?>
                    <h3 style="font-size: var(--hph-text-xl); font-weight: var(--hph-font-semibold); color: var(--hph-gray-900); margin: 0 0 var(--hph-margin-lg) 0;">
                        <?php echo esc_html($form['title']); ?>
                    </h3>
                    <?php endif; ?>
                    
                    <div>
                        <input 
                            type="text" 
                            placeholder="Your Name" 
                            style="width: 100%; padding: var(--hph-padding-md) var(--hph-padding-lg); border: 1px solid var(--hph-gray-300); border-radius: var(--hph-radius-lg); font-size: var(--hph-text-base);"
                            required
                        >
                    </div>
                    
                    <div>
                        <input 
                            type="email" 
                            placeholder="Your Email" 
                            style="width: 100%; padding: var(--hph-padding-md) var(--hph-padding-lg); border: 1px solid var(--hph-gray-300); border-radius: var(--hph-radius-lg); font-size: var(--hph-text-base);"
                            required
                        >
                    </div>
                    
                    <button 
                        type="submit" 
                        style="width: 100%; padding: var(--hph-padding-md) var(--hph-padding-lg); background: var(--hph-primary); color: var(--hph-white); border: none; border-radius: var(--hph-radius-lg); font-size: var(--hph-text-base); font-weight: var(--hph-font-semibold); cursor: pointer; transition: all 300ms ease; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);"
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='translateY(0)'"
                    >
                        <?php echo esc_html($form['button_text'] ?? 'Submit'); ?>
                    </button>
                </form>
            </div>
            
            <?php elseif ($image): ?>
            <!-- Image -->
            <img 
                src="<?php echo esc_url($image['url']); ?>" 
                alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
                style="width: 100%; height: auto; border-radius: var(--hph-radius-lg); box-shadow: 0 10px 30px rgba(0,0,0,0.1);"
                loading="lazy"
            >
            
            <?php else: ?>
            <!-- Buttons for split layout -->
            <div style="display: flex; flex-direction: column; gap: var(--hph-gap-lg); align-items: stretch;">
                <?php foreach ($buttons as $button): 
                    $btn = wp_parse_args($button, array(
                        'text' => 'Button',
                        'url' => '#',
                        'style' => 'white',
                        'size' => 'xl',
                        'icon' => '',
                        'target' => '_self'
                    ));
                    
                    $btn_styles = array(
                        'display: inline-flex',
                        'align-items: center',
                        'justify-content: center',
                        'text-decoration: none',
                        'font-weight: var(--hph-font-semibold)',
                        'border-radius: var(--hph-radius-lg)',
                        'transition: all 300ms ease',
                        'box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1)',
                        'width: 100%'
                    );
                    
                    // Button size
                    $btn_styles[] = 'padding: var(--hph-padding-lg) var(--hph-padding-2xl)';
                    $btn_styles[] = 'font-size: var(--hph-text-lg)';
                    
                    // Button style
                    switch($btn['style']) {
                        case 'outline-white':
                            $btn_styles[] = 'background-color: transparent';
                            $btn_styles[] = 'color: var(--hph-white)';
                            $btn_styles[] = 'border: 2px solid var(--hph-white)';
                            break;
                        case 'white':
                        default:
                            $btn_styles[] = 'background-color: var(--hph-white)';
                            $btn_styles[] = 'color: var(--hph-primary)';
                            $btn_styles[] = 'border: 2px solid var(--hph-white)';
                    }
                ?>
                <a 
                    href="<?php echo esc_url($btn['url']); ?>"
                    style="<?php echo implode('; ', $btn_styles); ?>"
                    <?php if ($btn['target'] !== '_self'): ?>target="<?php echo esc_attr($btn['target']); ?>"<?php endif; ?>
                    onmouseover="this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.transform='translateY(0)'"
                >
                    <?php if ($btn['icon']): ?>
                    <i class="<?php echo esc_attr($btn['icon']); ?>" style="margin-right: var(--hph-margin-sm);"></i>
                    <?php endif; ?>
                    <span><?php echo esc_html($btn['text']); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        
        <!-- Centered/Boxed/Minimal Layout -->
        <div style="<?php echo $animation ? 'animation: fadeInUp 0.8s ease-out;' : ''; ?>">
            
            <?php if ($badge): ?>
            <!-- Badge -->
            <div style="margin-bottom: var(--hph-margin-lg);">
                <span style="display: inline-block; padding: var(--hph-padding-sm) var(--hph-padding-md); <?php echo $layout === 'boxed' ? 'background: var(--hph-primary-100); color: var(--hph-primary-700);' : 'background: rgba(255, 255, 255, 0.2); color: currentColor; backdrop-filter: blur(10px);'; ?> border-radius: var(--hph-radius-full); font-size: var(--hph-text-sm); font-weight: var(--hph-font-semibold);">
                    <?php echo esc_html($badge); ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if ($headline): ?>
            <!-- Headline -->
            <h2 style="margin: 0 0 var(--hph-margin-lg) 0; font-size: var(--hph-text-5xl); font-weight: var(--hph-font-bold); line-height: var(--hph-leading-tight);">
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
            <div style="margin: 0 0 var(--hph-margin-2xl) 0; font-size: var(--hph-text-base); line-height: var(--hph-leading-relaxed); opacity: 0.85;">
                <?php echo wp_kses_post($content); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($buttons)): ?>
            <!-- Buttons -->
            <div style="display: flex; flex-wrap: wrap; gap: var(--hph-gap-lg); align-items: center; <?php echo $button_justify; ?>">
                <?php foreach ($buttons as $button): 
                    $btn = wp_parse_args($button, array(
                        'text' => 'Button',
                        'url' => '#',
                        'style' => $layout === 'boxed' ? 'primary' : 'white',
                        'size' => 'xl',
                        'icon' => '',
                        'target' => '_self'
                    ));
                    
                    $btn_styles = array(
                        'display: inline-flex',
                        'align-items: center',
                        'justify-content: center',
                        'text-decoration: none',
                        'font-weight: var(--hph-font-semibold)',
                        'border-radius: var(--hph-radius-lg)',
                        'transition: all 300ms ease',
                        'box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1)'
                    );
                    
                    // Button size
                    switch($btn['size']) {
                        case 'xl':
                            $btn_styles[] = 'padding: var(--hph-padding-lg) var(--hph-padding-2xl)';
                            $btn_styles[] = 'font-size: var(--hph-text-lg)';
                            break;
                        default:
                            $btn_styles[] = 'padding: var(--hph-padding-md) var(--hph-padding-xl)';
                            $btn_styles[] = 'font-size: var(--hph-text-base)';
                    }
                    
                    // Button style
                    if ($layout === 'boxed') {
                        switch($btn['style']) {
                            case 'outline':
                                $btn_styles[] = 'background-color: transparent';
                                $btn_styles[] = 'color: var(--hph-primary)';
                                $btn_styles[] = 'border: 2px solid var(--hph-primary)';
                                break;
                            case 'primary':
                            default:
                                $btn_styles[] = 'background-color: var(--hph-primary)';
                                $btn_styles[] = 'color: var(--hph-white)';
                                $btn_styles[] = 'border: 2px solid var(--hph-primary)';
                        }
                    } else {
                        switch($btn['style']) {
                            case 'outline-white':
                                $btn_styles[] = 'background-color: transparent';
                                $btn_styles[] = 'color: var(--hph-white)';
                                $btn_styles[] = 'border: 2px solid var(--hph-white)';
                                break;
                            case 'white':
                            default:
                                $btn_styles[] = 'background-color: var(--hph-white)';
                                $btn_styles[] = 'color: var(--hph-primary)';
                                $btn_styles[] = 'border: 2px solid var(--hph-white)';
                        }
                    }
                ?>
                <a 
                    href="<?php echo esc_url($btn['url']); ?>"
                    style="<?php echo implode('; ', $btn_styles); ?>"
                    <?php if ($btn['target'] !== '_self'): ?>target="<?php echo esc_attr($btn['target']); ?>"<?php endif; ?>
                    onmouseover="this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.transform='translateY(0)'"
                >
                    <?php if ($btn['icon']): ?>
                    <i class="<?php echo esc_attr($btn['icon']); ?>" style="margin-right: var(--hph-margin-sm);"></i>
                    <?php endif; ?>
                    <span><?php echo esc_html($btn['text']); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
        </div>
        
        <?php endif; ?>
        
        <?php if ($layout === 'boxed'): ?>
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
</style>
<?php endif; ?>